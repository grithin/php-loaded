<?
namespace Grithin;

/* About
Basic logging with format option and size limit option
*/

use \Grithin\Strings;
use \Grithin\Config;
use \Grithin\Files;
use \Grithin\SDLL;
use \Grithin\Tool;
use \Grithin\Debug;

class Log{
	public $openned = false;
	protected $file;
	protected $mode;
	protected $fh = false;
	use SDLL; # so as only to create the log file when it is used
	/*	params
	< options >:
		< format > < format log should take.  'json' or 'pretty'.  >
		< log_folder_create > < whether to create the log folder if it does not exist >
	*/
	public function __construct($options=[]){
		$defaults = [
			'format'=>'pretty',
			'log_folder_create' => true];
		$this->options = array_merge($defaults, (array)$options);
	}

	protected function load(){
		if(empty($this->options['file'])){
			if(empty($this->options['folder'])){
				$this->options['folder'] = Config::root_folder().'log/';
			}
			if(!is_dir($this->options['folder']) && $this->options['log_folder_create']){
				mkdir($this->options['folder']);
			}
			$this->options['file'] = $this->options['folder'].date('Ymd').'.log';
		}

		$this->file = $this->options['file'];

		if(!is_file($this->file)){
			$this->clear();
			clearstatcache(false, $this->file);
		}
	}
	protected function open(){
		if(isset($this->options['size_limit'])
			&& filesize($this->file) > Strings::byteSize($this->options['size_limit'])
		){
			Files::write($this->file, ''); #< clear file
			if($this->fh){
				fclose($this->fh);
				$this->fh = null;
			}
		}
		if(!$this->fh){
			$this->fh = fopen($this->file,'a+');
		}
		return $this->fh;
	}
	protected function write($data, $format=null){
		$fh = $this->open();

		$format = $format ? $format : $this->options['format'];

		switch($format){
			case 'json':
				fwrite($fh, \Grithin\Tool::json_encode($data)."\n");
			break;
			case 'pretty':
				fwrite($fh, \Grithin\Debug::pretty($data, $this->caller())."\n");
			break;
			case 'plain':
				fwrite($fh, $data."\n");
			break;
			default:
				throw new \Exception('Unrecognized format for log : '.$format);
			break;
		}
	}
	protected function get(){
		$content = file_get_contents($this->file);
		if($this->options['format'] == 'json'){
			$lines = explode("\n", $content);
			$logs = array_map(['\Grithin\Tool','json_decode'], array_slice($lines, 0, -1)); #< exclude last line, which is blank
			return array_reverse($logs); #< return lots in order of most recent first
		}else{
			return $content;
		}
	}
	protected function clear(){
		Files::write($this->file, '');
	}

	public function __destruct(){
		if($this->fh){
			fclose($this->fh);
		}
	}
	# because this is a SingletonDefault, have to account for the two ways of calling a method in determining the caller
	private function caller(){
		$trace = debug_backtrace(null,7);
		if($trace[6]['function'] == '__callStatic' && $trace[6]['class'] = 'Grithin\\Log'){
			return $trace[6];
		}else{
			return $trace[4];
		}
	}
}
