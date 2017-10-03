<?
require_once __DIR__ . '/../vendor/autoload.php';

#+	standard constants {
define('ROOT_PATH', __DIR__);
define('ROOT_FOLDER', ROOT_PATH.'/');

if(!defined('PUBLIC_PATH')){
	define('PUBLIC_PATH', ROOT_PATH.'/public');
	define('PUBLIC_FOLDER', PUBLIC_PATH.'/');
}
#+ }

#+	standard config {
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT &~E_DEPRECATED);

date_default_timezone_set('UTC');
#+	}

use \Grithin\Db;

\Grithin\GlobalFunctions::init();