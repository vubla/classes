<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';


new PHPUnit_Framework_TestSuite("AnyScrapeObjectTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);
/**
 * @group scrape
 */
class TestAnyScrapeObject extends AnyScrapeObject {
   
    public function testGethostname(){
        return $this->getHostname();
    }
    
}

class AnyScrapeObjectTest extends BaseDbTest 
{

    protected $wid = 1;
	
    function setUp() {
    	
        $this->wid = 1;
        $this->buildDatabases();
     
    }
    
    function tearDown() {
        
        $this->dropDatabases();
    }
    
    
    function testGethostname()
    {
        $s = new TestAnyScrapeObject(1);
        $res = $s->testGethostname();
        $expected = 'everlight.dk';
        $this->assertEquals($expected, $res);
    }
   
}
    



