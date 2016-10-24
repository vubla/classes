<?php 



abstract class MagentoAttributeBase 
{
    protected static $_entities = array();
   
    
    
    static function clear()
    {
        self::$_entities = array();
    }
    
  
    //abstract static function get();
    
    protected $_client;
    protected function __construct($client)
    {
        $this->_client = $client;
       
    }
}