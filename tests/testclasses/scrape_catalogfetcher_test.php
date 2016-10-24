<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';


$suite  = new PHPUnit_Framework_TestSuite("CatalogFetcherTest");

class TestCatalogFetcher extends CatalogFetcher {
    
    
    public function getNextCategory() {
        
    }
    public function getNextProduct() {
        
    }
    
    public function createProduct($d)
    {
        return parent::createProduct($d);
    }
    
   
    
}


class TestVublaXmlParser extends VublaXmlParser {
    public function parseXml($d){
        return parent::parseXml($d);
    }
}

class CatalogFetcherTest extends BaseDbTest {
   
    public $data;
    function setUp(){
       
       
        $c =  new TestVublaXmlParser('random');
        $this->buildDatabases();
        
          
        $this->data = $c->parseXml(str_replace('&nbsp;', '', file_get_contents('../stdoscommerce.xml')));
        
    }
    
    function tearDown(){
         $this->dropDatabases();
    }
    
    
    
   function testCreateProduct(){
        $data = $this->data;
        
        $p = new TestCatalogFetcher(1);
    
        $result = $p->createProduct($data['product'][0]); 
        
        $this->assertInstanceOf('Product', $result);
        $this->assertEquals('1', $result->pid);
        $this->assertCount(12,$result->options);
        $this->assertEquals('products_price',$result->options[2]['name']);
        $this->assertEquals('pid',$result->options[11]['name']);
        $this->assertEquals('299.9900',$result->options[2]['value']['name']);
        $this->assertEquals('4',$result->categories[0]);
        
        //echo json_encode($result); exit;
    }

    function testCreateProduct2(){
           $data = $this->data;
         $p = new TestCatalogFetcher(1);
       
        $result = $p->createProduct($data['product'][1]); 
        
        $this->assertInstanceOf('Product', $result);
        $this->assertEquals('3', $result->pid);
        $this->assertCount(11,$result->options);
        $this->assertEquals('products_model',$result->options[0]['name']);
        $this->assertEquals('images/microsoft/msimpro.gif',$result->options[1]['value']['name']);
        $this->assertEquals('products_image',$result->options[1]['name']);
    }
}
    
?>
