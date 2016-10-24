<?php


abstract class VublaIterator implements Iterator
{
    protected $var = array();

    public function __construct($array)
    {
        if (is_array($array)) {
            $this->var = $array;
        }
    }

    public function map($func, $arg = null, $arg2 = null){
        $list = array();
        
        foreach ($this->var as $elem) {
            
             $list[] = ($elem->$func($arg));
        }
        return $list;
    }
    
    public function getFromAll($name){
        $list;
        foreach ($this->var as $elem) {
             $list[] =$elem->$name;
        }
        return $list;
    }
    
    public function rewind()
    {
        reset($this->var);
    }
  
    public function current()
    {
        $var = current($this->var); 
        return $var;
    }
  
    public function key() 
    {
        $var = key($this->var);
        return $var;
    }
  
    public function next() 
    {
        $var = next($this->var);
        return $var;
    }
  
    public function valid()
    {
        $key = key($this->var);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }
    
    public function set($arr){
      
        if(is_array($arr)){
            $this->var = $arr;
        } else {
            throw new Exception("An array was not inserted. I got : " .print_r($arr,true), 1);
            
        }
    }
    
  
	
	public function delete($key){
		unset($this->var[$key]);
	}
	
        /**
         * 
         * @return int 
         */
	public function length(){
		return sizeof($this->var);
	}
        
        /**
         *
         * @return T 
         */
    public function shift(){
        return array_shift($this->var);
    }

}


?>