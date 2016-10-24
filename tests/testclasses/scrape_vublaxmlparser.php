<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';

$suite  = new PHPUnit_Framework_TestSuite("VublaXmlParserTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);



class TestVublaXmlParser extends VublaXmlParser {
    
    
    function getRaw($ignore = null){
        
        return file_get_contents('../stdoscommerce.xml');
    }
    // This should only be used to test getRaw() while the method above is used by everyone else. 
     function getRaws(){
        return parent::getRaw('oscommerce2.3.1.crawler.vubla.com/vubla.php');
    }
   
    function parseXML($xml) { return parent::parseXML($xml); }

    
	
}


class VublaXmlParserTest extends BaseDbTest 
{
    private $test_max_prod;
    private $initial;
    
    function setUp() {
        $this->buildDatabases();
        VOB::setTarget(VOB::TARGET_NONE);
        $this->c = new TestVublaXmlParser("yada");
        
    }
    
    function tearDown() {
        unset($this->c);
        $this->dropDatabases();
        
    }
    
    function testGetRaw()
    {
        ob_start();
    
        $result = $this->c->getRaws();
        $ob = ob_get_clean();  
        $this->assertEquals('',$ob);
        
        $this->assertInternalType('string', $result);    
        $this->assertTrue(strpos($result, '<product>') !== false); // there most be a product
        $this->assertTrue(strpos($result, 'Matrix') !== false); // The movie is in the XML
          
    }
    
    function testParseXML(){
        ob_start();
        
        $data = $this->c->getRaw('some');
        $result = $this->c->parseXML($data);
        $ob = ob_get_clean();  
        $this->assertEquals('',$ob);
                  
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertCount(28, $result['product']);
        $this->assertCount(21, $result['category']);
        for($i = 0; $i < 28; $i++){
            if($i == 1 || $i == 2) continue;
            $this->assertEquals($i+1, $result['product'][$i]['pid']);
        }
             
    }
    
    function testGetCatalog(){
       ob_start();
        $result = $this->c->getCatalog();
         $ob = ob_get_clean();  
        $this->assertEquals('',$ob);
         $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertCount(28, $result['product']);
        $this->assertCount(21, $result['category']);
        for($i = 0; $i < 28; $i++){
            if($i == 1 || $i == 2) continue;
            $this->assertEquals($i+1, $result['product'][$i]['pid']);
        }
    }
    
    
 
    
}




