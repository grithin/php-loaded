<?
namespace Grithin;

class Redis{
	use \Grithin\SDLL;

	/* params
		< connection_info > : ['127.0.0.1',6379, 1, NULL, 100]
		< options >
			< prefix > < used to prefix all keys >
			< db_id > < the redis db id >
	*/
	function __construct($connection_info, $options=[]){
		$this->connection_info = $connection_info;
		$this->options = $options;
	}

	function load(){
		$this->prefix = $this->options['prefix'];
		$this->under = new \Redis;

		$return = call_user_func_array([$this->under,'connect'],$this->connection_info);

		if($options['db_id']){
			$this->under->select($this->options['db_id']);
		}
	}
	/// sees if cache is working
	protected function check(){
		$this->under->set('on',1);
		if(!$this->under->get('on')){
			return false;
		}
		$this->under->delete('on');
		return true;
	}

	protected function expire($name, $time){
		$name = $this->prefix.$name;
		return $this->under->expire($name, $time);
	}
	protected function delete($name){
		return $this->under->delete($this->prefix.$name);
	}
	protected function get($name){
		return json_decode($this->under->get($this->prefix.$name), true);
	}
	/*
	@param	options	< expire offset >
	@param	options	< options array >
	*/
	protected function set($name, $data, $options = null){
		$name = $this->prefix.$name;
		$data = json_encode($data);
		if($options === null){
			$options = []; // NULL will just immediately delete it
		}elseif(!is_array($options)){
			$options = (int)$options;//Redis php lib will fail on string number
		}
		return $this->under->set($name,$data,$options);
	}


	protected function watch($name){
		$name = $this->prefix.$name;
		return $this->under->watch($name);
	}
	/*
	@ex
		this->watch('test')
		this->sef_if_unchanged('test', 'bob')
		this->unwatch('test')
	*/
	protected function sef_if_unchanged($name, $data, $options){
		$name = $this->prefix.$name;
		$data = json_encode($data);
		if($options !== null && !is_array($options)){
			$options = (int)$options;//Redis php lib will fail on string number
		}
		return $this->under->multi()->set($name,$data,$options)->exec();
	}
	function unwatch(){
		$name = $this->prefix.$name;
		return $this->under->unwatch($name);
	}

	///for getting and potentially updating cache
	/**
	allows a single client to update a cache while concurrent connections just use the old cache (ie, prevenut multiple updates).  Useful on something like a public index page with computed resources - if 100 people access page after cache expiry, cache is only re-updated once, not 100 times.

	@param	name	name of cache key
	@param	value_function	function to call in case cache needs updating or doesn't exist
	@param	options
			[
				update => relative time after which to update
					ex: "+20 seconds"
				deadline => relative time after which cache expires
					ex: "+40 seconds"
			]
	@note	options passed to update_function, so include any additional data desired

	@ex
		$waiter = function(){
			sleep(5);
			return 'bob';
		};
		$cache->recache_get('test',$waiter,['update'=>'+2 seconds', 'deadline'=>'+20 seconds']);
	*/
	protected function recache_get($name,$value_function,$options){
		$recache = $this->get($name.':recache');
		if($recache){ # is set, check times
			if(time() > $recache['next_update']){ # need to update
				# do the update if ...
				if(
					!$recache['updating'] # not currently updating
				){
					return $this->recache_set($name, $value_function, $options);	}	}

			$value = $this->get($name);
			if($value !== false){ # first update may take a while, allow multiple people to make it
				return $value;	}
		}
		return $this->recache_set($name,$value_function,$options);
	}
	protected function recache_set($name,$value_function,$options){

		$options['deadline'] = $options['deadline'] ? $options['deadline'] : '+20 seconds';
		$recache =[
			'next_update' => (new \Grithin\Time($options['update']))->unix,
			'updating' => true ];

		$expiry_offset = (new \Grithin\Time($options['deadline']))->unix - time();

		# indicate updating
		$this->set($name.':recache', $recache, $expiry_offset);
		# run value_function
		$value = call_user_func_array($value_function,$options);
		# update cache
		$this->set($name,$value,$expiry_offset);

		$recache['updating'] = false;
		$this->set($name.':recache', $recache, $expiry_offset);

		return $value;
	}
}