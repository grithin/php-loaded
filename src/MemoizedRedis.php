<?php
namespace Grithin;

use Exception;

trait MemoizedRedis{
	use Memoized;

	public function memoized__init($connection_info, $options=[]){
		$this->redis = new Redis($connection_info, $options);
	}
	public function memoized__has_key($key){
		return $this->redis->exists($key);
	}
	public function memoized__get_from_key($key){
		return $this->redis->get($key);
	}
	public function memoized__set_key($key, $result){
		$this->redis->set($key, $result);
	}
	public function memoized__unset($name, $arguments){
		$key = $this->memoized__make_key($name, $arguments);
		$this->redis->delete($key);
	}
}