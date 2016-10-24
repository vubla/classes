<?

require_once CLASS_FOLDER.'/languages/da.php';

class Text {
    
    static function _($name, $class = null, $replaces = null){
         
       $real = strtolower($name);
       $real = str_replace(' ', '_', $real);
               
        if(!is_null($class)){
            $class = strtolower($class);
            if(defined($class.'_'.$real)){
                $val = constant($class.'_'.$real);
            }
           
        } 
        
        if(!isset($val) && defined($real)){
            $val = constant($real);
        }
        
        if(!isset($val))
        {
            $val = $name;
        }
       
       if(is_array($replaces)){
           
           foreach ($replaces as $key => $value) {
          
               $val  = str_replace('{%'.$key.'}', $value, $val);
           }
    
       }
        
       return $val;
    }
     
   
    static function _e($name, $class = null, $replaces = null){
         
       echo self::_($name, $class, $replaces);
       
    }
     
    static function new_e($name, $replaces = null, $secid = null){
        echo self::new__($name, $replaces , $secid)     ;
          
    } 
    
    
    static function new__($name, $replaces = null, $secid = null){
           
       $val = _($name);    
       if(is_array($replaces)){
           
           foreach ($replaces as $key => $value) {
          
               $val  = str_replace('{%'.$key.'}', $value, $val);
           }
    
       }
       return $val;
       
    }
    
    static function substrword($str, $length, $minword = 1)
    {
        $sub = '';
        $len = 0;
   
        foreach (explode(' ', $str) as $word)
        {
            
            $part = (($sub != '') ? ' ' : '') . $word;
            
            if (strlen($word) > $minword && strlen($sub)+strlen($word) > $length)
            {
                break;
      	    }
            
            $len += strlen($part);
            $sub .= $part;
           
            
   	    }
        if($length == 0){
            return '';
        }
   	    return $sub . (($len < strlen($str)) ? '...' : '');
    }
    
    static  function time($time){
        return strftime("%H:%M", $time);        
    }
    
    static function datetime($time){
        return self::date($time) .' '. self::time($time);
    }
    
    static function date($time){
        return strftime("%e/%m-%Y", $time);        
    }
     
    static function money($number)
    {
        return money_format('%i', $number);
    }
}

?>
