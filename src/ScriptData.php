<?
namespace Grithin;

use \Grithin\Tool;
use \Grithin\Files;
use \Grithin\Config;


/* About
For saving data associated with the running script, without the need to manage the storage
*/


/* Example
$data = ScriptData::get_or_init(['start_time'=>time(), 'count'=>0]);
$data['count'] += mt_rand(0,99);
# `start_time` will always be set through the get_or_init method, but the added count may not result in a `set` call - thus the point of a `get_or_init` function instead of just a default value
if($data['count'] > 10){
	ScriptData::set($data);
}
*/


class ScriptData{
	function get_scripts_storage_directory(){
		$storage_dir = Config::root_folder().'storage/';
		if(!is_dir($storage_dir)){
			mkdir($storage_dir);
		}
		$scripts_storage_dir = $storage_dir.'scripts/';
		if(!is_dir($scripts_storage_dir)){
			mkdir($scripts_storage_dir);
		}
		return $scripts_storage_dir;
	}
	function filename_default(){
		$data_file = basename($_SERVER['SCRIPT_FILENAME']);
		$folders = implode('__',array_slice(explode('/', dirname($_SERVER['SCRIPT_FILENAME'])), -2)); # get last two dirs
		return ($folders.'__'.$data_file);
	}
	function file_path($filename=''){
		if(!$filename){
			$filename = self::filename_default();
		}
		$filename = $filename.'.json';
		$storage_base = self::get_scripts_storage_directory();
		return $storage_base.$filename;
	}
	# gets existing, or intiializes data and returns initial data
	function get_or_init($initial_data, $filename=null){
		$data = self::get($filename);
		if(!$data){
			self::set($initial_data, $filename);
			$data = $initial_data;
		}
		return $data;
	}
	function get($filename=null){
		$file = self::file_path($filename);
		clearstatcache(false, $file);
		if(!is_file($file)){
			Files::write($file, '');
			return [];
		}
		$file_content = file_get_contents($file);
		return $file_content ? (array)Tool::json_decode($file_content) : [];
	}
	function set($data, $filename=null){
		Files::write(self::file_path($filename), Tool::json_encode((array)$data));
	}
	function remove($filename=null){
		$file = self::file_path($filename);
		clearstatcache(false, $file);
		if(is_file($file)){
			unlink($file);
		}
	}
}