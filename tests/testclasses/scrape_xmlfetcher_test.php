<?php


require_once '../vublamailer.php';
require_once '../basedbtest.php';
new PHPUnit_Framework_TestSuite("XmlFetcherTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);


class TestXmlFetcher extends XmlFetcher{
   
   
       public function __construct(){
            CatalogFetcher::__construct(1);
            $this->wid = 1;

        }
        
   
    function getHostname() {
            return "oscommerce2.3.1.crawler.vubla.com";
    }
}

class XmlFetcherTest extends BaseDbTest 
{

    protected $wid = 1;
	
    function setUp() {
    	
        $this->wid = 1;
        $this->buildDatabases();
     
    }
    
    function tearDown() {
        
        $this->dropDatabases();
    }
    
  
    
    function testGetNextProduct()
    {
       
        $c = new TestXmlFetcher(1);
        $i = 0;
        while($p = $c->getNextProduct()){
            $this->assertInstanceOf('Product', $p);
            $i++;
            if($i > 28){
                $this->assertFail();
            }
        }
        $this->assertEquals(28, $i);
    }
    
   
    
   
    function testGetNextCategory()
    {
         
        $cl = new TestXmlFetcher(1);
        $i = 0;
        while($c = $cl->getNextCategory()){
            $this->assertInstanceOf('Category', $c);
            $i++;
             if($i > 21){
                $this->assertFail();
            }
        }
        $this->assertEquals(21, $i);
    }
	
}
    



