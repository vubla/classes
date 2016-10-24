<?php

abstract class BaseController {
    public $vars;
    public $view;
    public $layout;
    public $js = array();
    public $javascript = array();
    public $css = array();
    function __construct(){
        $this->vars = new StdClass();
    }

    function standard(){
        $this->view = 'default';
        $this->layout = '';
    }
    
    protected function redirect($controller = null, $task = null, $url = LOGIN_URL, $param = ''){
        if(is_array($param))
        {
            $param = '?'.http_build_query($param);
        }
        if(is_null($url)) 
        {
            $url = LOGIN_URL;
        }
        if(is_null($controller)){
            echo header('Location: '. $url.$param);
            exit;
        }
        
        if(is_null($task)){
            echo header('Location: '. $url.'/'.$controller.$param);
            exit;
        }
        
        echo header('Location: '. $url.'/'.$controller.'/'.$task.$param);
        exit;
    }
    
    function addJs($var,$code = false){
    	  //$var can contain either a link or pure code
    	  if($code) {
    	  		$this->javascript[] = $var;
    	  }
    	  else { $this->js[] = $var; }
    }
    
    function addCss($link){
        $this->css[] = $link;
    }
    
    function getWid(){
        $uid = $_SESSION['uid'];
        return Vpdo::getVdo(DB_METADATA)->fetchOne('Select wid from customers where id = ?', array($uid));
    }

    function getRequestVariable($varName,$default = null,$strict = false)
    {
        if(isset($_POST))
        {
            if(array_key_exists($varName, $_POST))
            {
                return $_POST[$varName];
            }
        }
        if(isset($_GET))
        {
            if(array_key_exists($varName, $_GET))
            {
                return $_GET[$varName];
            }
        }
        
        if($strict)
        {
            throw new VublaException('Request variable "'.$varName.'" not found.');
        }
        return $default;
    }

}

