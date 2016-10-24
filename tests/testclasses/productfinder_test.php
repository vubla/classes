<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';


$suite  = new PHPUnit_Framework_TestSuite("ProductFinderTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);




class ProductFinderTest extends BaseDbTest 
{
    private $test_max_prod;
    private $initial;
    
    function setUp() {
        $this->buildDatabases();
        $vdo = VPDO::getVdo(DB_METADATA);
        @$vdo->exec('drop database phpunit___temp');
    }
    
    function tearDown() {
       
        $this->dropDatabases();
    }
    
    function testConstructer()
    {
         
          $this->assertTrue(true);
          
          
    }
    
    /*
   
    function testGetProduct(){
        $data =TestCrawler::getTestData();
        $p = new ProductFinder(1);
       
        $result = $p->getProduct($data['product'][0]); 
        
        $this->assertInstanceOf('Product', $result);
        $this->assertEquals('1', $result->pid);
        $this->assertCount(12,$result->options);
        $this->assertEquals('products_price',$result->options[2]['name']);
        $this->assertEquals('pid',$result->options[11]['name']);
        $this->assertEquals('299.9900',$result->options[2]['value']['name']);
        $this->assertEquals('4',$result->categories[0]);
        
        //echo json_encode($result); exit;
    }

    function testGetProduct2(){
        $data =TestCrawler::getOtherTestData();
        $p = new ProductFinder(1);
       
        $result = $p->getProduct($data['product'][0]); 
        
        $this->assertInstanceOf('Product', $result);
        $this->assertEquals('1', $result->pid);
        $this->assertCount(2,$result->options);
        $this->assertEquals('pid',$result->options[0]['name']);
        $this->assertEquals('Matrix',$result->options[1]['value']['name']);
        $this->assertEquals('name',$result->options[1]['name']);
    }

    */
    
}


