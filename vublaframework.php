<?php 
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}
class VublaFramework {

    static $user = null;
    function start($enable_login = true){
        
        if (!isset($_SESSION['uid']) ) {
        	User::defaults(); // Set default cookies
        }
        
        if($enable_login){
            self::$user = new User();
        }
        
        $task = 'standard';
        $controller = 'default';
  
        if(isset($_GET['task'])){
            if(ctype_alnum($_GET['task'])){
                $task = $_GET['task'];
            }
        } 
        
        
        if(isset($_GET['controller'])){ 
            if(ctype_alnum($_GET['controller'])){
                $controller = $_GET['controller'];
            }
            
        }
        
        if(isset($_POST['task'])){
            if(ctype_alnum($_POST['task'])){
                $task = $_POST['task'];
            }
        } 
        
        
        if(isset($_POST['controller'])){ 
            if(ctype_alnum($_POST['controller'])){
                $controller = $_POST['controller'];
            }
            
        }
       
        if($_SESSION['logged'] !== true && ($enable_login)){
                // Override the controllers and tasks if user not logged in, unless its the user controller.
                $controller = 'user';
                if(	$task != 'register' 		&& 
                	$task != 'validateuserdata' && 
                	$task != 'activate' 		&& 
                	$task != 'resetpassword' 	&& 
                	$task != 'setpassword' 		&& 
                	$task != 'resendactivation' && 
                	$task != 'configuration'    &&
                	$task != 'signup'           &&
                	$task != 'signupsave'       &&
                  /*$task != 'recover'          && */
                	$task != 'callback'			)
                {
                    $task = 'standard';
                }
              
        } 
        
        // If the user has filled in the login form.
        if(isset($_POST['vubla_login'])){
            $task = 'login';
            $controller = 'user';
        }
        
            
        /// Check if crawled or redirect to status
        if($enable_login){
        	// If the login is diusabled then we are not on the public admin site. 
        	$wid = User::getWid($_SESSION['uid']);
		    $db = vpdo::getVdo(DB_METADATA);
        	$on_config = $db->fetchOne('select on_config from customers where wid = ?', array($wid));
			if($on_config && $controller != 'configuration'){
			    $controller = 'configuration';
                $currentstep = $db->fetchOne('select name from 
                                        configuration_steps cs 
                                    inner join 
                                        configuration_progress cp 
                                    on 
                                        cs.id = cp.conf_steps_id 
                                    where 
                                        uid = ? 
                                    order by 
                                        cp.id desc limit 1 ', array($_SESSION['uid']));   
                                        
                $task = $currentstep.'step';
			} else if(!$on_config && $controller == 'configuration'){
			    $controller = 'default';
			}
            
            if($task == 'step'){
                  $db->exec('insert into configuration_progress (uid, conf_steps_id) values ('.(int) $_SESSION['uid'].','. 2 .')');
                  $task = 'webshopstep';
            }
                     
                  
        
        }
      
        if(!file_exists('controllers/'.$controller.'Controller.php')){
        	// If the file does not exists use default
            $controller = 'default';
        }
        include('controllers/'.$controller.'Controller.php');
        
        $controllername = $controller.'Controller';

        if(!class_exists($controllername)){
             $controller = 'default';
             include_once('controllers/'.$controller.'Controller.php');
             $controllername = $controller.'Controller';   
        } 
        
        $controllerclass = new $controllername();
 
        if(!method_exists($controllerclass, $task)){
            $task = 'standard';
        }
        $controllerclass->$task();
        
        $vars = $controllerclass->vars;
        if(!is_object($vars)){
            $vars = new StdClass();
        }
        if(!isset($vars->task) || $vars->task == '') $vars->task = $task; // Make sure the view gets this info. 
        
        $js = array_unique($controllerclass->js);
        $javascript = array_unique($controllerclass->javascript);
        $css = array_unique($controllerclass->css);
        
        $view = $controllerclass->view;
        $layout = $controllerclass->layout;
        $controllerclass = null;
        $this->view = 'views/'.$view.'View.php';
        
        if($view != 'none'){
   
            if($_SESSION['logged'] === true || (!$enable_login) || !is_null($layout)){
                
                include($layout.'layout.php');
            } else {
                include('layout_offline.php');
            }
        }
    
    }
    
    
    function link($controller = null, $task = null, $gets = null, $return = false){
       // echo $url . '/?controller=' .$controller . '&task='.$task;
        $url = LOGIN_URL;
        if(is_null($task)){
             $link =  $url . '/' .$controller  . $gets;
           
        } else {
            $link = $url . '/' .$controller . '/'.$task .  $gets;
        }
        
        if($return){
            return $link;
        }
        echo $link;
        
    
    }
    
    // Returns a formatted time string
    
    // Returns a formatted time string
    function time($time){
        return Text::time($time);        
    }
    
    function datetime($time){
        return Text::datetime($time);
    }
    
     function date($time){
        return Text::date($time);        
    }
     
  
}
