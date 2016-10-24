<?php



class VOB {
    
    const TARGET_LOG = 1;
    const TARGET_STDERR = 2;
    const TARGET_STDOUT = 3;
    
    const TARGET_NONE = 4; 
    const TARGET_BUFFER = 5;
    
    /**
     *
     * @var string 
     */
    private static $buffer; 
    private static $verbose = false; 
    
    
    private static $currentOutputTarget = self::TARGET_NONE;
    
    static function _n($s){
        self::_($s."\n");
    }
    
    static function v_n($s){
        if(self::$verbose)
            self::_n($s);
    }
    
    static function v_($s){
        if(self::$verbose)
            self::_($s);
    }
    
    static function _($s){
        switch(self::$currentOutputTarget)
        {
            case self::TARGET_STDOUT:
                echo $s;
                break;
            case self::TARGET_NONE:
                break;
            case self::TARGET_LOG:
                VublaLog::_($s);
                break;
            case self::TARGET_STDERR:
                error_log($s);
                break;
             case self::TARGET_BUFFER:
                self::$buffer .= $s;
                break;
        }
    }
    
    static function setTarget( $t){
        self::$currentOutputTarget = $t;
    }
    
    static function flush(){
        return self::$buffer;
    }
    
    static function setVerbose($verbosity)
    {
        self::$verbose = $verbosity;
    }
    
    static function getVerbose()
    {
        return self::$verbose;
    }
    
}
?>
