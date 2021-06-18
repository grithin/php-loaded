<?php
namespace Grithin;

/*
Create a temporary file which is destroyed on script close.
Defaults to path `/dev/shm`, the linux shared memory location (ram files)

File methods are passed to the underlying `SplFileObject` object either directly or with a prefixed 'f', so `$tmp_file->write()` will work
*/
/* ex
$tmp_file = new TempFile; # defaults to opening with `r+`
echo $tmp_file->filepath;
$tmp_file->write('test');
*/

class TempFile {
	public function __construct($options=[]){
		$this->options = array_merge(['directory'=>'/dev/shm', 'prefix'=>'TempFile-', 'open'=>'r+'], $options);
		$this->path = tempnam($this->options['directory'], $this->options['prefix']);
		if($this->options['affix']){
			$path_new = $this->path.$this->options['affix'];
			rename($this->path, $path_new);
			$this->path = $path_new;
		}
		$this->filepath = &$this->path;
		if($this->options['open']){
			$this->open($this->options['open']);
		}
	}
	public function __call($method, $args){
		if($this->File){
			if(method_exists($this->File, $method)){
				return call_user_func_array([$this->File, $method], $args);
			}elseif(method_exists($this->File, 'f'.$method)){
				return call_user_func_array([$this->File, 'f'.$method], $args);
			}
		}
		throw new \Exception('Method not found "'.$method.'"');
	}
	public $File;
	public $mode;
	# @note SplFileObject can not be used with functions that take a file handler object.
	public function open($mode){
		if(!$this->File || $this->mode != $mode){
			$this->File = new \SplFileObject($this->filepath, $mode);
			$this->mode = $mode;
		}
		return $this;
	}
	public function reopen(){
		$this->File = new \SplFileObject($this->filepath, $this->mode);
	}
	public function close(){
		if($this->File){
			unset($this->File);
		}
	}
	public function __destruct(){
		$this->destroy();
	}
	public function destroy(){
		if($this->File){
			$this->close();
			unlink($this->filepath);
		}
	}
	public function read_all(){
		clearstatcache(true, $this->filepath);
		$this->reopen();
		$this->rewind();
		$size = $this->getSize();
		if($size){
			return $this->read($size);
		}
		return '';
	}
}