<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';
new PHPUnit_Framework_TestSuite("MagentoScraperTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);


class TestMagentoScraper extends MagentoScraper {
  
    function testGetFetcher(){
        return $this->fetcher;
    }
    
}

class MagentoScraperTest extends BaseDbTest 
{


	
    function setUp() {
    	$this->buildDatabases();
      
     
    }
    
    function tearDown() {
        $this->dropDatabases();
      
      
    }
    
    
    function testConstruct()
    {
         
     //   $c = new TestMagentoScraper(1);
        $this->assertTrue(true);
    }
    
   
}
    



