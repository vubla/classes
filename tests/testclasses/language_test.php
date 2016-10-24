<?php
require_once '../vublamailer.php';
require_once '../basedbtest.php';
$suite  = new PHPUnit_Framework_TestSuite("Languagetest");



class Languagetest extends BaseDbTest 
{
       function setUp() {
        $this->buildDatabases();
        $this->vdo = VPDO::getVdo(DB_METADATA);
          Language::$_lang = null;
           Settings::setGlobal('admin_language',2);
             unset($_SESSION);
        unset($_GET);
        unset($_POST);
        Language::$_lang = null;
        Language::$isInit = null;
        Language::$wid = null;
    }
    
    function tearDown() {
       
        $this->dropDatabases();
        unset($_SESSION);
        unset($_GET);
        unset($_POST);
        Language::$_lang = null;
        Language::$isInit = null;
        Language::$wid = null;
    }
    
       function _testLangActuallyChanges()
    {
        
          @ $this->dropSpecificDatabase(DB_PREFIX.'3');   
          $this->getVdo()->exec('CREATE DATABASE '.DB_PREFIX.'3');
        $this->getVdo()->exec('USE '.DB_PREFIX.'3');
        $this->buildSpecificDatabase('phpunit_3');
        settings::setLocal('admin_language', 1, 1);
        settings::setLocal('admin_language', 2, 3);
        Settings::setGlobal('admin_language',2);
       
        Language::init(1);
        $this->assertEquals('da_DK', getenv("LANG"));
        $this->assertEquals('da_DK',setlocale (LC_ALL, '0'));
        $this->assertEquals('da_DK',setlocale (LC_MESSAGES, '0'));
        $this->assertEquals('Pakke', __('Pakke'));
        $this->assertEquals(1, Language::get()->getId());
   
  
        
        Language::loadLanguage(1);
        $this->assertEquals('da_DK', getenv("LANG"));

        Language::reset();
        Language::loadLanguage(2);
        $this->assertEquals('en_US', getenv("LANG"));
     
        Language::reset();
        Language::loadLanguage(1);
        $this->assertEquals('da_DK', getenv("LANG"));
           
     
    }
    
     function _testLangActuallyChangesUsingWids()
    {
       //7 $this->buildSpecificDatabase('phpunit_3');
        Settings::setGlobal('admin_language',2);
        settings::setLocal('admin_language', 1, 1);
        settings::setLocal('admin_language', 2, 3);
        
        Language::init(1);
        $this->assertEquals('da_DK', getenv("LANG"));
        $this->assertEquals('Pakke', __('Pakke'));
  
        Language::init(3);
        $this->assertEquals('en_US', getenv("LANG"));


        Language::init(1);
        $this->assertEquals('da_DK', getenv("LANG"));
        $this->assertEquals('Pakke', __('Pakke'));
        
        
          Language::init(3);
        $this->assertEquals('en_US', getenv("LANG"));
        $this->assertEquals('Plan', __('Pakke'));

        Language::init(1);
        $this->assertEquals('da_DK', getenv("LANG"));
        
          Language::init(3);
        $this->assertEquals('en_US', getenv("LANG"));
        $this->assertEquals('Plan', __('Pakke'));

        Language::init(1);
        $this->assertEquals('da_DK', getenv("LANG"));
        Language::init();
        $this->assertEquals('en_US', getenv("LANG"));
           $this->dropSpecificDatabase('phpunit_rasmunit3');   
    }
    
    
    function testInitSession(){
        
        Settings::setLocal('admin_language',1,1);
        $_SESSION['uid'] = 1;
      
        $this->assertEquals('1',Language::get()->getId());
        
    }
    
    function testInitSpecificWid(){
        Settings::setLocal('admin_language',1,1);
        Language::init(1);
        Language::init();
        $this->assertEquals('1',Language::get()->getId());
   
    }    
    
    function testInitNoSpec(){
          Settings::setLocal('admin_language',1,1);       
        Language::init();
        Language::init();
        $this->assertEquals('2',Language::get()->getId());
        Language::init(1);
        Language::init();
        $this->assertEquals('1',Language::get()->getId());
 
    }
    

    function testInitGetLocale(){
        Settings::setLocal('admin_language',1,1);
        $_GET['locale'] = 'en_US';
        Language::init();
        $this->assertEquals('2',Language::get()->getId());
    } 
    function testInitGetLocaleTrick(){
        $_SESSION['uid'] = 1;
        Settings::setLocal('admin_language',1,1);
        $_GET['locale'] = 'en_US';
        Language::init();
        $this->assertEquals('1',Language::get()->getId());
       
    } 
   
   
    function testInitGetLangIdTrick(){
        Settings::setLocal('admin_language',1,1);
        $_SESSION['uid'] = 1;
        $_GET['lang_id'] = '2';
        Language::init();
        $this->assertEquals('1',Language::get()->getId());
    }
    
    function testInitIso(){
        Settings::setLocal('admin_language',1,1);
        $_GET['iso'] = 'da';
        Language::init();
        Language::init();
        $this->assertEquals('1',Language::get()->getId());
        $this->assertEquals('1',Language::get()->getId());
        $this->assertEquals('1',Language::get()->getId());
    }

    function testInitGetLangId(){
        $_GET['lang_id'] = '1';
        Language::init();
        $this->assertEquals('1',Language::get()->getId());
        $this->assertEquals('1',Language::get()->getId());
        $this->assertEquals('1',Language::get()->getId());
    }
    function testInitPostLangId(){
        $_POST['lang_id'] = '1';
        Language::init();
        Language::init();
        $this->assertEquals('1',Language::get()->getId());
    }
    
    function testInitServerVars(){
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = "da";
        Language::init();
        $this->assertEquals('1',Language::get()->getId());
        Language::init();
        $this->assertEquals('1',Language::get()->getId());
    }
    
 
}


