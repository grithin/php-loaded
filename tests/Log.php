<?
# run with `phpunit Log.php`

require __DIR__.'/loader.php';

use PHPUnit\Framework\TestCase;

use Grithin\Log;


class MainTests extends TestCase{
	function __construct(){
		Log::singleton();
	}
	function setUp(){
		Log::clear();
	}

	function assertEqualsCopy($x, $y, $error_message='not equal'){
		if($x != $y){
			pp('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!error');
			pp([$x,$y]);
			throw new Exception($error_message);
		}
	}
	function test_pretty(){
		Log::primary()->options['format'] = 'pretty';
		$line = __LINE__;
		Log::write('bob');
		$log = Log::get();
		$this->assertEquals(preg_match('@'.preg_quote(__FILE__).'@', $log), 1, 'file not found on log line');
		$this->assertEquals(preg_match('@:'.($line+1).'\]@', $log), 1, 'line not found on log line');
		$this->assertEquals(preg_match('@ : bob@', $log), 1, 'bob not found on log line');
	}
	function test_plain(){
		Log::primary()->options['format'] = 'plain';
		Log::write('bob');
		$log = Log::get();
		$this->assertEquals($log, "bob\n", 'file not found on log line');
	}
	function test_json(){
		Log::primary()->options['format'] = 'json';
		Log::write(['bob'=>'123']);
		Log::write(['bob'=>'456']);
		$log = Log::get();
		$this->assertEquals($log, [['bob'=>'456'], ['bob'=>'123']], 'file not found on log line');
	}
}