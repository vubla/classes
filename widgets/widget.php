<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}


abstract class Widget extends BaseTemplateObject {
    protected $wid;
    public $type;
    public $id;
    
    abstract function generateHtml();
    
    abstract function generateJS();
    
    public function __construct($wid,$id){
       $this->wid = $wid;
		$this->id = $id;
       
    }
      
    static function create ($wid,$type,$id){
        $name = ucfirst($type.'Widget');
        return new $name($wid,$id);
    }
    
     public function __get($name){
            if(isset($this->$name)){
                return $this->$name;
            } elseif(isset($_GET[$name])) {
                $ss = $_GET[$name];
                return $ss;
            }  elseif(isset($_GET['getvar']->$name)){
               $ss =  $_GET['getvar']->$name;
               return $ss;
            } elseif(isset($_GET['postvar']->$name)){
               $ss =  $_GET['getvar']->$name;
               return $ss;
            }
            return null;
        }
}
?>