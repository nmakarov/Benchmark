<?php

class BenchmarkTest extends CDbTestCase
{
	public function testBenchmark()
	{
		// skip PHPUnit dots alignment problem
		print "\n";

		Yii::import('ext.Benchmark');
		$bench = Benchmark::getInstance();

		// expected FALSE, since there's no active mark in progress
		$this->assertFalse($bench->cutoff());

		// start benchmarking batch
		$bench->start('Benchmark testing');

		// a simple loop
		for ($i=1; $i<=5; $i++)
		{
			$this->assertTrue($bench->mark('First operation'));
			usleep(100);
			// 'mark' with optional text parameter
			$this->assertTrue($bench->mark('Second operation', $i));
			usleep(20000);
			$this->assertTrue($bench->cutoff());
		}

		// see how well lock/save/load stuff works
		$bench->lock_marks();

		for ($i=1; $i<=2; $i++)
		{
			for ($j=1; $j<=5; $j++)
			{
				$bench->mark('Third operation', "of the $i run");
				usleep(100);
				$bench->cutoff();
			}

			$bench->save('/tmp', $i);
		}

		$bench->load('/tmp');

		// finalize benchmarking batch and print out some results
		$bench->kaput();

/*

Expected output: 

 --- Benchmark testing ---
2012-06-24 17:06:31
[    0.0003] First operation
[    0.0211] Second operation 1
[    0.0002] First operation
[    0.0211] Second operation 2
[    0.0002] First operation
[    0.0211] Second operation 3
[    0.0002] First operation
[    0.0212] Second operation 4
[    0.0002] First operation
[    0.0212] Second operation 5
[    0.0002] Third operation of the 1 run
[    0.0002] Third operation of the 1 run
[    0.0002] Third operation of the 1 run
[    0.0002] Third operation of the 1 run
[    0.0002] Third operation of the 1 run
[    0.0002] Third operation of the 2 run
[    0.0002] Third operation of the 2 run
[    0.0002] Third operation of the 2 run
[    0.0002] Third operation of the 2 run
[    0.0002] Third operation of the 2 run

Benchmark testing totals:
memory: 8.85MB
peak  : 9.02MB
[    0.1120] Execution time
[    0.0002] 0.0011 / 5 First operation
[    0.0211] 0.1057 / 5 Second operation
[    0.0002] 0.0020 / 10 Third operation
--- kaput ---
 
 */

	}
}