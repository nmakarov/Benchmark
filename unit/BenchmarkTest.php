<?php

class BenchmarkTest extends CDbTestCase
{
	public function testBenchmark()
	{
		// to skip PHPUnit dots alignment problem
		print "\n";

		Yii::import('ext.Benchmark.Benchmark');
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

		// finalize benchmarking batch and print out some results
		$bench->kaput();
	}
}