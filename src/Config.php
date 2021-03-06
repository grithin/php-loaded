<?php
namespace Grithin;
/*
Configuration loading

Get config vars with optional config file path and fallback getter
*/

class Config{
	use \Grithin\Traits\SingletonDefault;
	public $config = [];
	function __construct($options=[]){
		#+ defaults {
		if(empty($options['main'])){
			$options['main'] = 'main';
		}
		if(empty($options['folder'])){
			$options['folder'] = self::root_folder().'config/';
		}
		if(empty($options['fallback'])){
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
		if(isset($this->loaded[$path])){ # in case multiple keys are within same file, prevent multiple is_file checks if non-existent
			return;
		}
		$this->loaded[$path] = true;

		$file = $this->options['folder'].$path.'.php';

		if(is_file($file)){
			$config = require_once($file);
			$this->config = array_merge($this->config, $config);
			return $config;
		}
	}
	protected $loaded_path_vars = [];
	protected function get($key){
		# prefixed, deal with prefix
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
		# either non-prefixed or prefix has been dealt with
		if(!array_key_exists($key, $this->config)){
			$this->config[$key] = $this->options['fallback']($key, $path);
		}
		return $this->config[$key];
	}

	/** like `get`, but, in case the fallback throws an exception, catch, ignore and return null */
	protected function try($key){
		try{
			return $this->get($key);
		}catch(\Exception $e){
			return null;
		}
	}

	/** default to throwing an exception if the key is missing */
	public function getter_fallback($key, $prefix=''){
		$prefix_message = $prefix ? ' with prefix "'.$prefix.'"' : '';
		throw new \Exception('Missing Config key "'.$key.'"'.$prefix_message);
	}
}
