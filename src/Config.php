<?
namespace Grithin;
/*
Configuration loading

Get config vars with optional config file path and fallback getter
*/

class Config{
	use \Grithin\SingletonDefault;
	function __construct($options=[]){
		#+ defaults {
		if(!$options['main']){
			$options['main'] = 'main';
		}
		if(!$options['folder']){
			$options['folder'] = self::root_folder().'config/';
		}
		if(!$options['fallback']){
			$options['fallback'] = [$this, 'getter_fallback'];
		}

		#+ }
		$this->options = $options;
		$this->load($this->options['main']);
	}
	static function root_folder(){
		if(!defined('ROOT_FOLDER')){
			define('ROOT_FOLDER', dirname($_SERVER['SCRIPT_FILENAME']).'/');
		}
		return ROOT_FOLDER;

	}
	protected $loaded = [];
	protected function load($path){
		if($this->loaded[$path]){ # in case multiple keys are within same file, prevent multiple is_file checks if non-existent
			return;
		}
		$this->loaded[$path] = true;

		$file = $this->options['folder'].$path.'.php';

		if(is_file($file)){
			require_once($file);
		}
	}
	protected $loaded_path_vars = [];
	protected function get($key){
		if(strstr($key, ':') !== false){
			list($path, $key) = explode(':', $key, 2);
			if(!$path){
				$key = $path;
			}
			if(array_key_exists($key, $_ENV)){
				return $_ENV[$key];
			}
			if($path){
				$this->load($path);
			}
		}
		if(!array_key_exists($key, $_ENV)){
			$_ENV[$key] = $this->options['fallback']($key);
		}
		return $_ENV[$key];
	}
	public function getter_fallback($key){
		return null;
	}
}