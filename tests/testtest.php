<?
# run with `phpunit Redis.php`

$_ENV['root_folder'] = realpath(dirname(__FILE__).'/../').'/';
require $_ENV['root_folder'] . '/vendor/autoload.php';

use PHPUnit\Framework\TestCase;

use \Grithin\Debug;
use \Grithin\Time;
use \Grithin\Arrays;
use \Grithin\Redis;


\Grithin\GlobalFunctions::init();

ppe('bob');

class MainTests extends TestCase{
	function test_basic_methods(){
		$Redis = Redis::singleton(['127.0.0.1',6379, 1, NULL, 100]);
		$this->assertEquals(true, $Redis->check(), 'Redis not on');

		$value = 'monkey';
		$key = 'test1';
		$Redis->set($key, $value);
		$this->assertEquals($value, $Redis->get($key), 'set/get failure');

		$value = ['monkey'];
		$key = 'test1';
		$Redis->set($key, $value);
		$this->assertEquals($value, $Redis->get($key), 'set/get failure');

		$Redis->set($key);
		$this->assertEquals(false, $Redis->get($key), 'set/get failure');
	}
	function test_basic_methods_with_prefix(){
		$Redis = Redis::init('redis1', ['127.0.0.1',6379, 1, NULL, 100]);
		$RedisPrefix = Redis::singleton('redis2', ['127.0.0.1',6379, 1, NULL, 100], ['prefix'=>'prefix_test']);
		$this->assertEquals(true, $Redis->check(), 'Redis not on');
		$this->assertEquals(true, $RedisPrefix->check(), 'Redis not on');

		$value = 'monkey';
		$key = 'test1';
		$Redis->set($key, $value);
		$this->assertEquals($value, $Redis->get($key), 'set/get failure');
		$this->assertEquals(false, $RedisPrefix->get($key), 'set/get failure');
	}
}