<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}
/**
 * Log class. Use to log everything
 * Make a method for each log
 */
class VublaLog {
	
	
		
		
	static public $msg;
	static private $isSaved = false;


	static private $subject = null;
	
	static public function sendMail()
	{
	
		self::$msg .= "<br><br><pre>" . print_r($_SERVER, true) . "</pre>";
		if(self::$subject)
		{
			$subject = self::$subject;
		} 
		else 
		{
			$subject = 'Log Message';
		}
        VublaMailer::sendOnargiEmail($subject, self::$msg); 
		
		self::$msg = null;
	}
	
	

	
	static function output(
    ){
	   echo  self::$msg;
	}
	
		
	
	public static function _setSubject($subject)
	{
		$log = self::getLog()->subject = $subject;
	}
	

	
		
	public static function _($msg_)
	{
        self::$msg .= $msg_;
		
	}
	
	public static function _n($msg)
	{
		self::_($msg . '<br/>'."\n");
	}
	
	public static function saveAll()
	{
       if(self::$msg)	
       {
           error_log(self::$msg);
       }
	   if(defined('VUBLA_DEBUG') && VUBLA_DEBUG)
	   {
	       echo '<br>Vubla debug is enabled and we output then not exit<br>' .PHP_EOL;
           echo '<br>Ouputtin message:<br>';
           echo PHP_EOL;
	       echo self::$msg;
           echo PHP_EOL;
           echo '<br>Outputting ob:<br>';
           echo PHP_EOL;
           echo ob_end_flush();
	   } 
	   else 
	   {    
           self::sendMail();
	   }
	   @ob_end_clean();
	  
	   exit;
	  
	}
	
	
		

	public static function killGently($name = 'default')
	{
		self::$isSaved = true;
		self::$msg = null;
	}
	
	
	
	
}
