<?php



class Language {
    
    static $isInit = false;
    static $_lang = null;
    static $wid = null;
    public static function  init($wid = null)
    {
       if(!self::$isInit or $wid != self::$wid)
       {
            $vpo = vpdo::getVdo(DB_METADATA);
            $lang_id = 0;
            
            if(!is_null( self::$wid) && is_null($wid)){
                $wid = self::$wid;
            }
            
            
            
            if(isset($_SESSION['uid']) && $_SESSION['uid'] && is_null($wid))
            {
                $wid = User::getWid($_SESSION['uid']);
               
            } 
               if(!is_null( ($wid))){
                self::$wid = $wid;
            }
            if(!empty($_POST)){
                $data = $_POST;
            } else if (isset($_GET)){
                $data = $_GET;
            }
            
            if (!is_null($wid)) 
            {
                 $lang_id = Settings::getLocal('admin_language',$wid);    
            }
            else  if (isSet($data["lang_id"])) 
            {
                $lang_id = $data["lang_id"];
            } 
            else  if (isSet($data["locale"])) 
            {
                $lang_id = $vpo->fetchOne('select id from languages where locale = ?', array($data["locale"]));
            } 
            else  if (isSet($data["iso"])) 
            {
                $lang_id = $vpo->fetchOne('select id from languages where iso = ?', array($data["iso"]));
            }
            else if (isset($_SESSION['iso']))
            {
                $lang_id = $vpo->fetchOne('select id from languages where iso = ?', array( substr($_SESSION['iso'],0,2)));
            }
            else if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
            {
                $lang_id = $vpo->fetchOne('select id from languages where iso = ?', array( substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2)));
            }
            
            if(!self::loadLanguage($lang_id))
            {
                $lang_id = Settings::get('admin_language');; // Default
                self::loadLanguage($lang_id);
            }
 
        
           
       }
    }
    public static function reset($wid = null){
        self::$wid = null;
        self::$_lang = null;
        self::$isInit = false;
        
        self::init($wid);
    }
    public static function loadLanguage($lang_id){
        $vpo = vpdo::getVdo(DB_METADATA);
        $stm = $vpo->prepare('select * from languages where id = ?');
        $stm->execute(array($lang_id));
        self::$_lang = $stm->fetchObject('Language');
        $stm->closeCursor();
        if(!(self::$_lang))
        {
            return false;
        } else {
            self::$_lang->start();
            self::$isInit = true;
            return true;
        }
    }
    
    public static function get()
    {
        if(!self::$isInit){
            self::init();
        
        }
        if(!is_object(self::$_lang))
        {
            self::init();
        }
        return self::$_lang;
    }
    
    function start()
    {
          //echo "started as: " . $this->locale;
	      putenv("LANGUAGE=");
          $encoding = ""; //.utf8
          $_SESSION['iso'] = $this->iso;
          putenv("LC_ALL={$this->locale}{$encoding}");
          putenv('LANG='.$this->locale.$encoding);
          $res = setlocale(LC_ALL, $this->locale.$encoding);
          if($res != $this->locale.$encoding){

              throw new Exception("We attempted to set the encoding to: " . $this->locale.$encoding . " but it was to: " . $res);
          }
          bindtextdomain("vubla", CLASS_FOLDER."/locale");
          textdomain("vubla");
    }
    
    function save($wid){
      
        Settings::setLocal('admin_language', $this->id, $wid);
    }
    
   
    
    public function __call($method, $args) {
       
        if(strpos ($method, 'get') === 0 ){
            $count = 1;
            $name = strtolower(str_replace('get', '', $method, $count));
            if(!property_exists($this, $name))
            {
                throw new VublaException("Class property did not exist");
            }
            return $this->$name;
        }   else {
            if(!property_exists($this, $name))
            {
                throw new VublaException("Method did not exist");
            }
        }
    }
    
}

function __($name, $params = null,$secid = null)
{
    
    return Text::new__($name, $params,$secid);
}

function _e($name, $params = null,$secid = null)
{
    Text::new_e($name, $params,$secid);
}

function vbl_number_format($num,$dec){
    switch(Language::get()->getid())
    {
        case 1:
            return number_format($num,$dec,',','.');
        case 2: 
            return number_format($num,$dec,'.',',');
    }
    
}



