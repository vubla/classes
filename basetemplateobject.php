<?php

class BaseTemplateObject 
{
    /**
     * @var SearchResult
     */
    protected  $searchResult; 
    protected $wid;
    function __construct ($wid,SearchResult $search){
        $this->wid = $wid;
        $this->searchResult = $search;
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
           $ss =  $_GET['postvar']->$name;
           return $ss;
        }
        return null;
    }
    
    public function getShopFormAction($host = null){
    	
		$link = '';
        if($this->host || $host)
        {
        	
            $link = 'http://';
            
          
			if(!is_null($this->return_host) && is_null($host)){
	    		$host = str_replace(array('http://','https://'), '', $this->return_host);
			} 
            if($host)
            {
                $link .= $host;
            } 
            else 
            {
                $link .= $this->host;
            }
			
        } 
      
        if(is_null($this->param)) $this->param = 'keywords';
        if(is_null($this->file)) $this->file = '';
        $this->file = urldecode($this->file);
		if(strpos($this->file, '/') !== 0) $this->file = '/'.$this->file;
		
        //var_dump($this->replaceOverlap($link, $this->file)); exit;
        return $this->replaceOverlap($link, $this->file);
    }
	
	function findOverlap($str1, $str2){
	  $sl1 = strlen($str1);
	  $sl2 = strlen($str2);
	  $max = max(array($sl1,$sl2));
      $min = min(array($sl1,$sl2));
	  for($i = 0 ; $i <= $min ; $i++) {
		    $s1 = substr($str1, $i+$sl1-$min);
		    $s2 = substr($str2, 0, $min-$i);
		    if($s1 == $s2){
		      return $s1;
		    }
            //echo $s1 . ' != ' . $s2 . '<br />';
	  }
	  //exit;
	  return '';
	}
	
	function replaceOverlap($str1, $str2){
	  	$overlap = $this->findOverlap($str1, $str2);
	    $str1 = substr($str1, 0, strlen($str1)-strlen($overlap));
	    $str2 = substr($str2, strlen($overlap));
	    return $str1.$overlap.$str2;
	}
	
    public function getShopLink($extra_params = array(), $host = null,$additionalPath = ''){
        if(is_null($extra_params)) $extra_params = array();
       	$params = array();
		
		if(!is_null($this->return_host)){	    	
	   		$params["return_host"] = $this->return_host;
		}
		if(!is_null($this->vubla_url)){         
            $params["vubla_url"] = $this->vubla_url;
        }
	    $link = $this->getShopFormAction($host) . $additionalPath;
        $link .= '?';
        
        $params[$this->param] = $this->q;
        
        
     
        if(isset($_GET['getvar']))
        {
            foreach($_GET['getvar'] as $key=>$val)
            {
                if(is_object($val)) $val = $val;
                $params[$key] =  $val;   
            }
        }
  
        if(isset($_GET['postvar']))
        {
          
            foreach($_GET['postvar'] as $key=>$val)
            {
                 if(is_object($val)) $val = (array)$val;
                $params[$key] = $val;   
            }
        }
         $params = array_merge($params,$extra_params);
    
   
        return $link. http_build_query($params);
    }
    
    public function modifyBuyNowLink($initialLink,$additionalPath = ''){
        $arr = explode('?', $initialLink);
        if(sizeof($arr) > 2) {
            throw new VublaException('Too many "?" in url');
        }
        $finalLink = $arr[0];
        
        /*
        if(strpos($finalLink, '/', strlen($finalLink)-1)  === strlen($finalLink)-1 && strpos($additionalPath, '/') === 0) {
            
        }
         * */
        $finalLink .= $additionalPath;
        
        return $finalLink;
    }
/*
    function recursiveMerge($arr1, $arr2)
    {
        foreach ($arr1 as $key => $value) {
            if(!is_array($arr2[$key]) && !is_array($value))
            {
                 unset($arr1[$key]);   
            }
            else 
            {
                array('eq_options'=>array('category'=>array('dvd'=>'dvd')))
            }
            
        }
       
    }
*/
}
