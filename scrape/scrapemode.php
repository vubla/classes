<?php


abstract class ScrapeMode {
    
    
    private static $instance_id;
    
    private static $instance;
    
    final protected function __construct(){
        
    }
    
    abstract function getPostfix();
    
    static function get(){
        if(self::$instance == null){
             self::set('full');
        }
        return self::$instance;
    }     
    
    static function set($identifier){
        if(self::$instance_id != $identifier)
        {
            $name = $identifier . 'scrapemode';
            self::$instance = new $name();
        } 
        self::$instance_id = $identifier;
     
    }
    
    /**
     * 
     */
    abstract function __toString();
    
    /**
     * Short method to get the postfix
     */
    static function getPF(){
        return self::get()->getPostFix();
    }
}
