<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';
new PHPUnit_Framework_TestSuite("MagentoSoapClientTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);


class TestMagentoFetcher2 extends MagentoFetcher{
    public $key = "45c088765ab907bc65869cba3656fb1b";
   
       public function __construct(){
            CatalogFetcher::__construct(1);
            $this->wid = 1;
            $this->hostname = "magento.crawler.vubla.com";
            try {
              $this->client =  $this->getClient();
            } catch (Exception $e){
                throw $e;
            }
           
        }
        
    protected function getClient() {
        if(is_null($this->client)){
             
             $this->client = new MagentoSoapClient($this->getHostname(),$this->key); 
        }
        
        return $this->client;
    }
    function getHostname() {
            return "magento.crawler.vubla.com";
    }
}

class MagentoSoapClientTest extends BaseDbTest 
{

    protected $wid = 1;
	
    function setUp() {
    	
        $this->wid = 1;
        $this->buildDatabases();
     
    }
    
    function tearDown() {
        
        $this->dropDatabases();
    }
    
    
    function testError()
    {
      //  settings::setLocal('hide_products_out_of_stock', 1, $this->wid);
        VOB::setTarget(VOB::TARGET_NONE);
        try 
        {
            $c = new MagentoFetcher(1);
            $this->assertFalse(true);
        } catch (Exception $e)
        {
            $this->assertTrue(true);
        }
      
    }
  
	
}
    



