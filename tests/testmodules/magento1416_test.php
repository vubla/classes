<?php



require '../basemoduletest.php';


$suite  = new PHPUnit_Framework_TestSuite("Magento1416Test");
/**
*
*
*/
class Magento1416Test extends BaseModuleTest {

	//function __construct(){ parent::__construct();}
     var $module_to_test = array('1.4', '1.6', '1.7');
     var $current;
     function setUp() {
      
    
		
    }
	 
	 function tearDown(){
	 	
	 }
    
	 function setEnabled($host,$value ){
	    $general_vpdo = new VPDO('vubla_metadata', 'vubla', 'TfAUwNsQ97PsvYCm',null, 'db.vubla.com');
		echo $wid = $general_vpdo->fetchOne("select id from webshops where hostname = ".$general_vpdo->quote($host));
		
		if(!$wid)
        {
            echo "Failed setting enable on: " .$host;
        }
		$wpdo = new VPDO('vubla_webshop_'.$wid, 'vubla', 'TfAUwNsQ97PsvYCm',null, 'db.vubla.com');
		$wpdo->exec("update settings set value = ".(int)$value." where name = 'enabled'");
		
	}
     
     function setKey($host ){
        $general_vpdo = new VPDO('vubla_metadata', 'vubla', 'TfAUwNsQ97PsvYCm',null, 'db.vubla.com');
        echo $wid = $general_vpdo->fetchOne("select id from webshops where hostname = ".$general_vpdo->quote($host));
        
        if(!$wid)
        {
            echo "Failed getting key for: " .$host;
        }
        $wpdo = new VPDO('vubla_webshop_'.$wid, 'vubla', 'TfAUwNsQ97PsvYCm',null, 'db.vubla.com');
     
        $wpdo->exec("update settings set value = 'test_api_key' where name = 'mage_api_key'");
        
    }
    
    function testAll()
    {
      // $this->deployModules('magento', '1.4.x-1.6.x');
       foreach ($this->module_to_test as $version){
           echo 'Deploying magento  1.4.0-1.7.x'.$version .PHP_EOL;
           $this->current = $version;
           $this->removeModules('magento', '1.4.0-1.7.x', $this->current);
           $this->deployModules('magento', '1.4.0-1.7.x', $this->current);
   
           $hostname = 'magento1.4.0-1.7.x'.$this->current.'.crawler.vubla.com';
           
           $this->_testContentEnabled();
           $this->_testGetLog($hostname);
           $this->setEnabled($hostname, 1); 
       }
       echo "line: " .__LINE__ .  PHP_EOL;
   }
	
	
   function _testContentEnabled(){
   		
		$this->setEnabled('magento1.4.0-1.7.x'.$this->current.'.crawler.vubla.com', 1);
		$result = file_get_contents('http://magento1.4.0-1.7.x'.$this->current.'.crawler.vubla.com/catalogsearch/result/?q=ottomen');
        if(is_null($result)) echo "Empty Results on line: ". __LINE__ .  PHP_EOL;
	    echo "line: ". __LINE__ . PHP_EOL;
		$this->assertContains('Furniture',$result, 'Version: ' . $this->current); // This category is there
	    echo "line: " .__LINE__ .  PHP_EOL;
	    $this->assertContains('Ottoman',$result, 'Version: ' . $this->current); // This category is there
	    //$this->assertContains('www.vubla.com',$result, 'Version: ' . $this->current );	
   } 
   
   function _testGetLog($hostname){
       $key = 'test_api_key';
       $this->setKey($hostname);
       $client = new MagentoSoapClient($hostname, 'test_api_key');
       $log = $client->call('vubla_search.fetch');
       $this->assertInternalType('array', $log, 'Version: ' . $this->current);
       $this->assertGreaterThan(3, count($log), 'Version: ' . $this->current);
   }
 
   
}

?>
