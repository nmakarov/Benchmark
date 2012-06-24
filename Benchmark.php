<?php

/**
 * Geting an instance:
 * 	$bench = Benchmark::getInstance();
 * 
 * Functions:
 * 
 * 	$bench->mark('something');
 * 	$bench->cutoff();
 * 
 * Batch wrapper functions:
 * 
 * 	$bench->start('lengthy operation name');
 * 	...
 * 	$bench->kaput();
 * 
 */

class Benchmark extends CComponent
{
	// public params
	public $output = array('stdout'); // db, syslog, yii-log, whatever
	protected $precision = 4;

	private $_start = NULL;
	private $_name = NULL;
	private $_marks = array();
	private $_current_mark = NULL;

	// singleton instance
	static $instance = NULL;

	public function init()
	{
		print "INIT!!!\n";
	}

	/**
	 * singleton construct
	 */
	static public function getInstance()
	{
		if (self::$instance === NULL)
			self::$instance = new self;
		return self::$instance;
	}

	/**
	 * If kaput() was not called properly, it will be called here:
	 */
	public function __destruct()
	{
		$this->kaput();
	}

	/**
	 * starts the benchmarking iteration
	 */
	public function start($name)
	{
		// finalize a previous batch, if any
		$this->kaput();

		// start the new one
		$this->_start = microtime(TRUE);
		$this->_name = $name;

		if (in_array('stdout', $this->output))
		{
			print "\n--- $name ---\n";
			printf("%s\n", date('Y-m-d H:m:s', $this->_start));
		}
		return TRUE;
	}

	/**
	 * ends the benchmarking iteration
	 * prints out the benchmarking results
	 */
	public function kaput()
	{
		// if there was no active benchmarking batch, do nothing
		if ($this->_start === NULL)
			return FALSE;

		// finalize on-going benchmark (if any)
		$this->cutoff();

		// how long it took for the whole batch
		$elapsed = round(microtime(TRUE) - $this->_start, $this->precision);

		// do something with the batch stats
		if (in_array('stdout', $this->output))
		{
			// overall batch stats
			print "\n{$this->_name} totals:\n";
			print $this->mem_usage();
			printf("[% 10.4f] Execution time\n", $elapsed);

			// individual benchmarks stats
			foreach($this->_marks as $mark=>$data)
			{
				$count = $data['count'];
				$total = array_reduce($data['times'], create_function('$x, $y', 'return $x+$y;'));
				$avg = round($total / $count, $this->precision);
				printf("[% 10.4f] %.4f / %d %s\n", $avg, $total, $count, $mark);
			}
			print "--- kaput ---\n";
		}

		// indicate that there is no active batch anymore
		$this->_start = NULL;

		// cleanup uther stuff as well
		$this->_name = NULL;
		$this->_marks = array();
		$this->_current_mark = NULL;

		return TRUE;
	}

	/**
	 * The most important function - mark the beginning of something
	 * @param a short description of what's going on
	 */
	public function mark($what, $extra='')
	{
		// finalize old mark (if any)
		$this->cutoff();

		// start a new one

		// prepare the results array for the current mark, if needed
		if ( ! array_key_exists($what, $this->_marks))
		{
			$this->_marks[$what] = array(
				'count' => 0,
				'times' => array(),
			);
		}

		// mark a start time and a name of the active benchmark
		$this->_marks[$what]['start'] = microtime(TRUE);
		$this->_marks[$what]['extra'] = $extra ? " $extra" : '';
		$this->_current_mark = $what;

		return TRUE;
	}

	/**
	 * The second most important function - mark the end of something
	 */
	public function cutoff()
	{
		// check if there's any active benchmarking going
		if ($mark = $this->_current_mark)
		{
			// update 'marks' array
			$this->_marks[$mark]['count']++;
			$elapsed = round(microtime(TRUE) - $this->_marks[$mark]['start'], $this->precision);
			$this->_marks[$mark]['times'][] = $elapsed;

			// TODO:: add config param for level of details
			if (in_array('stdout', $this->output))
			{
				printf("[% 10.4f] %s\n", $elapsed, $mark . $this->_marks[$mark]['extra']);
			}

			// no active marks:
			$this->_current_mark = NULL;

			return TRUE;
		}

		// just an indicator that nothing was done.
		return FALSE;
	}

	private function mem_usage()
	{
		$o = '';

		$mem = memory_get_usage();
		$o .= "memory: " . $this->format_size($mem) . "\n";
		$mem = memory_get_peak_usage();
		$o .= "peak  : " . $this->format_size($mem) . "\n";

		return $o;
	}

	public function format_size($s)
	{
		if ($s>1024*1024*1024)
			return round($s/1024/1024/1024,2) . 'GB';
		if ($s>1024*1024)
			return round($s/1024/1024,2) . 'MB';
		if ($s>1024)
			return round($s/1024,2) . 'KB';

	}

	public function save($folder)
	{
		if ( ! is_writable($folder))
		{
			return FALSE;
		}

		$this->cutoff();

		$file = $folder . '/saved_marks_' . getmypid() . '.txt';
		file_put_contents($file, serialize($this->_marks));
	}

	public function load($folder)
	{
		if ( ! is_readable($folder))
		{
			return FALSE;
		}

		$this->cutoff();

		foreach(glob($folder . '/saved_marks_*.txt') as $file)
		{
			$marks = unserialize(file_get_contents($file));

			foreach ($marks as $mark=>$data)
			{
				if ( ! array_key_exists($mark, $this->_marks))
				{
					$this->_marks[$mark] = array(
						'count' => 0,
						'times' => array(),
					);
				}

				$this->_marks[$mark]['count'] += $data['count'];
				$this->_marks[$mark]['times'] = array_merge(
					$this->_marks[$mark]['times'],
					$data['times']
				);
			}

			unlink($file);
		}
	}
}