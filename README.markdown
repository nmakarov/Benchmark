Benchmark
=========

Very simple yet useful benchmarking/logging class to be used in Yii framework. Since it uses no Yii-specific stuff, it is suitable for any other framework.

For me it is always annoying to look at the logs and see each line begins with 

```php
2012-06-23 19:06:27 ...
```
For me usable info is:
* when the script starts (timestamp)
* how long each operation goes (with brief description)
* grouped totals - how many time each op run and, total execution time and average execution time per operation

## Installation and configuration

Just download the script from http::github.com/nmakarov/Benchmark,
place Benchmark.php in /protected/extentions folder

## Usage

Include the extention and create an instance:
```php
Yii::import('ext.Benchmark');
$bench = Benchmark::getInstance();
```

Basic usage can be found in 'tests/unit/BenchmarkTest.php'

Cheers,

-Nick