<?php
session_start();
//setlocale(LC_ALL,'da_DK.UTF8', 'da','da_DK','da_DK.utf8');
putenv("LC_ALL=en_US");
putenv('LANG=en_US');
setlocale(LC_ALL, 'en_US');
mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');
checkConfig();



/**
 * This is a special class that autoloads the classes.
 * @author Vubla
 *
 */
class Autoload {
	public static $instance;
	public $src=array(CLASS_FOLDER);
	public $ext=array('.php');

	/* initialize the autoloader class */
	public static function init(){

        
		if(self::$instance==NULL){
			self::$instance=new self();
		}
		
		self::addDir(CLASS_FOLDER.'/data_layer');
		self::addDir(CLASS_FOLDER.'/interfaces');
        self::addDir(CLASS_FOLDER.'/traits');
        self::addDir(CLASS_FOLDER.'/search');
        self::addDir(CLASS_FOLDER.'/search/stringfilter');
        self::addDir(CLASS_FOLDER.'/widgets');
        self::addDir(CLASS_FOLDER.'/scrape');
        self::addDir(CLASS_FOLDER.'/exceptions');
        self::addDir(CLASS_FOLDER.'/solr');
		return self::$instance;
	}

	public static function addDir($path){
		if(!in_array($path,self::$instance->src));
		self::$instance->src[] = $path;
	}


	/* put the custom functions in the autoload register when the class is initialized */
	private function __construct(){
	    if(!defined('UNITTEST_MODE')){
	        // We cannot use clean in unittest mode
		  spl_autoload_register(array($this, 'clean'));
        }
		spl_autoload_register(array($this, 'dirty'));
	}

	/* the clean method to autoload the class without any includes, works in most cases */
	private function clean($class){
		$class = strtolower($class);
		spl_autoload_extensions(implode(',', $this->ext));
	    set_include_path(implode(PATH_SEPARATOR,$this->src)); 
	    spl_autoload($class);
		
	}

	// the dirty method to autoload the class after including the php file containing the class
	private function dirty($class){
	 global $docroot;
	 $class = strtolower($class);
	 foreach($this->src as $resource){
	 	foreach($this->ext as $ext){
	 		@include_once($docroot . $resource .'/' . $class . $ext);
	 	}
	 }
	 spl_autoload($class);
	}
	
	
}





function mb_replace($search, $replace, $subject, &$count=0) {
    if (!is_array($search) && is_array($replace)) {
        return false;
    }
    if (is_array($subject)) {
        // call mb_replace for each single string in $subject
        foreach ($subject as &$string) {
            $string = &mb_replace($search, $replace, $string, $c);
            $count += $c;
        }
    } elseif (is_array($search)) {
        if (!is_array($replace)) {
            foreach ($search as &$string) {
                $subject = mb_replace($string, $replace, $subject, $c);
                $count += $c;
            }
        } else {
            $n = max(count($search), count($replace));
            while ($n--) {
                $subject = mb_replace(current($search), current($replace), $subject, $c);
                $count += $c;
                next($search);
                next($replace);
            }
        }
    } else {
        $parts = mb_split(preg_quote($search), $subject);
        $count = count($parts)-1;
        $subject = implode($replace, $parts);
    }
    return $subject;
}



function exceptionHandler($exception) {

    // these are our templates
    $traceline = "#%s %s(%s): %s(%s)";
    $msg = "PHP Fatal error:  Uncaught exception '%s' with message '%s' in %s:%s\nStack trace:\n%s\n  thrown in %s on line %s";

    // alter your trace as you please, here
    $trace = $exception->getTrace();
    foreach ($trace as $key => $stackPoint) {
        // I'm converting arguments to their type
        // (prevents passwords from ever getting logged as anything other than 'string')
        $trace[$key]['args'] = array_map('gettype', $trace[$key]['args']);
    }

    // build your tracelines
    $result = array();
    foreach ($trace as $key => $stackPoint) {
        $result[] = sprintf(
            $traceline,
            $key,
            $stackPoint['file'],
            $stackPoint['line'],
            $stackPoint['function'],
            implode(', ', $stackPoint['args'])
        );
    }
    // trace always ends with {main}
    $result[] = '#' . ++$key . ' {main}';

    // write tracelines into main template
    $msg = sprintf(
        $msg,
        get_class($exception),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        implode("\n", $result),
        $exception->getFile(),
        $exception->getLine()
    );

    // log or echo as you please
   VublaLog::_n("Start Exception <br/><pre>".$msg."</pre> End exception <br />" .ob_get_clean());
   if(defined('VUBLA_DEBUG') && VUBLA_DEBUG){
       VublaLog::output();    
   } else {
       VublaLog::saveAll('Caught Exception in ' . basename($_SERVER['SCRIPT_FILENAME']));
   }
   
   exit();
}


set_exception_handler('exceptionHandler');






function objectsIntoArray($arrObjData, $arrSkipIndices = array()){
  // if input is object, convert into array
    $arrData = array();
  //  echo str_repeat(' ', $counter). '[' .PHP_EOL;
    if (is_object($arrObjData)) {
        $arrObjData = get_object_vars($arrObjData);
    }
    
    if (is_array($arrObjData)) {
        foreach ($arrObjData as $index => $value) {
            if (is_object($value) || is_array($value)) {
                $value = objectsIntoArray($value, $arrSkipIndices); // recursive call
            }
            if (in_array($index, $arrSkipIndices)) {
                continue;
            }
            $arrData[$index] = $value;
        }
    }
     // echo str_repeat(' ', $counter). ']'.PHP_EOL;;
    return $arrData;
}
?>