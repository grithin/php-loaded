<?
# run with `phpunit ScriptData.php`

require __DIR__.'/loader.php';

use PHPUnit\Framework\TestCase;

use Grithin\ScriptData;


class MainTests extends TestCase{
	function __construct(){

	}
	function tearDown(){
		ScriptData::remove();
	}

	function assertEqualsCopy($x, $y, $error_message='not equal'){
		if($x != $y){
			pp('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!error');
			ppe([$x,$y]);
			throw new Exception($error_message);
		}
	}
	function test_set_get(){
		ScriptData::remove();
		$data = ScriptData::set(['bob'=>'123']);
		$data = ScriptData::get();
		$this->assertEquals($data, ['bob'=>'123'], 'get set failure');
	}
	function test_remove(){
		$data = ScriptData::set(['bob'=>'123']);
		ScriptData::remove();
		$data = ScriptData::get();
		$this->assertEquals($data, [], 'get set failure');
	}

	function test_get_or_init(){
		ScriptData::remove();
		$data = ScriptData::get_or_init(['bob'=>'123']);
		$this->assertEquals($data, ['bob'=>'123'], 'init data wrong');
		$data = ScriptData::get();
		$this->assertEquals($data, ['bob'=>'123'], 'init data not set');
		$data['bob'] = '456';
		$data = ScriptData::set($data);
		$data = ScriptData::get_or_init(['bob'=>'123']);
		$this->assertEquals($data, ['bob'=>'456'], 'init data not using existing data');
	}
}
