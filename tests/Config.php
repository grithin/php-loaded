<?php
# run with `phpunit Config.php`

require __DIR__.'/loader.php';

use PHPUnit\Framework\TestCase;

use Grithin\Config;


class MainTests extends TestCase{
	function __construct(){
		Config::singleton();
	}

	function assertEqualsCopy($x, $y, $error_message='not equal'){
		if($x != $y){
			pp('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!error');
			ppe([$x,$y]);
			throw new Exception($error_message);
		}
	}
	function test_get(){
		$var = Config::get('bob');
		$this->assertEquals($var, '123', 'var `bob` wrong');
		$var = Config::get('sue');
		$this->assertEquals($var, '456', 'var `sue` wrong');
		$var = Config::get('sue:sue');
		$this->assertEquals($var, '456', 'var `sue` wrong with `sue` path');
		$var = Config::get('bill:bill');
		$this->assertEquals($var, '789', 'var `bill` wrong with `bill` path');
	}
	function test_over_load(){
		$var = Config::load('sue');
		$var = Config::get('sue');
		$this->assertEquals($var, 'abc', 'var `sue` wrong');
	}
	function test_fallback(){
		Config::primary()->options['fallback'] = function($key){
			return 'monkeys';
		};
		$var = Config::get('undefined');
		$this->assertEquals($var, 'monkeys', 'var `undefined` wrong with fallback');
	}
}
