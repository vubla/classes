<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';


$suite  = new PHPUnit_Framework_TestSuite("TemplateFooterTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);





class TemplateFooterTest extends BaseDbTest 
{
    private $test_max_prod;
    private $initial;
    protected $wid = 1;
    function setUp() {
        $this->buildDatabases();
        $this->initial = settings::get('max_search_results', 1);
        $this->test_max_prod = 50;
        settings::setLocal('max_search_results', $this->test_max_prod, 1);
    }

    function testGetFrom_1(){
        $data = new SearchResult();
        $data->number_of_products = 500;
        
        $_GET['search_offset'] = 0;
        $subject = new TemplateFooter(1,$data);
        $result = $subject->getFrom();
        $expected = 1;
        $this->assertEquals($expected, $result);
    }   
    function testGetFrom_2(){
        $data = new SearchResult();
        $data->number_of_products = 500;   
        $_GET['search_offset'] = 50;
        $subject = new TemplateFooter(1,$data);
        $expected = 51;
        $result = $subject->getFrom();
        $this->assertEquals($expected, $result);
    }
    
    function testGetFrom_3(){
        $data = new SearchResult();
        $data->number_of_products = 500;   
        $_GET['search_offset'] = 5001;
        $subject = new TemplateFooter(1,$data);
        $expected = 500;
        $result = $subject->getFrom();
        $this->assertEquals($expected, $result);
    }
    

   function testGetTo() {
        $data = new SearchResult();
        $data->number_of_products = 500;
        $_GET['search_offset'] = 1;
        $subject = new TemplateFooter(1,$data);
        $result = $subject->getTo();
        $expected = 51;
        $this->assertEquals($expected, $result);
        
        
        $data->number_of_products = 30;
        $subject = new TemplateFooter(1,$data);
        $result = $subject->getTo();
        $expected = 30;
        $this->assertEquals($expected, $result);
    }
    
    function testGetPagesList(){
        $data = new SearchResult();
        $data->number_of_products = 500;
        $_GET['search_offset'] = 1;
        $subject = new TemplateFooter(1,$data);
        $result = $subject->getPagesList();
  
        $this->assertInternalType('array',$result);
        $this->assertCount(10,$result);
        $this->assertEquals(1, $result[0]->number);
        $this->assertEquals(0, $result[0]->offset); 
        $this->assertEquals(true, $result[0]->marked);
        
        $this->assertEquals(2, $result[1]->number);
        $this->assertEquals(50, $result[1]->offset);
        $this->assertEquals(false, $result[1]->marked);
        
        $this->assertEquals(10, $result[9]->number);
        $this->assertEquals(450, $result[9]->offset);
        $this->assertEquals(false, $result[9]->marked);
    }
 
    function testGetPagesList_2(){
        $data = new SearchResult();
        $data->number_of_products = 500;
        $_GET['search_offset'] = 451;
        $subject = new TemplateFooter(1,$data);
        $result = $subject->getPagesList();
  
        $this->assertInternalType('array',$result);
        $this->assertCount(10,$result);
        $this->assertEquals(1, $result[0]->number);
        $this->assertEquals(0, $result[0]->offset);
        $this->assertEquals(false, $result[0]->marked);
        
        $this->assertEquals(2, $result[1]->number);
        $this->assertEquals(50, $result[1]->offset);
        $this->assertEquals(false, $result[1]->marked);
        
        $this->assertEquals(10, $result[9]->number);
        $this->assertEquals(450, $result[9]->offset);
        $this->assertEquals(true, $result[9]->marked);
    }
 
   function testGetPagesList_3(){
        $data = new SearchResult();
        $data->number_of_products = 500;
        $_GET['search_offset'] = 234;
        $subject = new TemplateFooter(1,$data);
        $result = $subject->getPagesList();
  
        $this->assertInternalType('array',$result);
        $this->assertCount(10,$result);
        $this->assertEquals(1, $result[0]->number);
        $this->assertEquals(0, $result[0]->offset);
        $this->assertEquals(false, $result[0]->marked);
        
        $this->assertEquals(2, $result[1]->number);
        $this->assertEquals(50, $result[1]->offset);
        $this->assertEquals(false, $result[1]->marked);
        
        $this->assertEquals(10, $result[9]->number);
        $this->assertEquals(450, $result[9]->offset);
        $this->assertEquals(false, $result[9]->marked);
       
        $this->assertEquals(true, $result[4]->marked);
    }
   
    function testGetPagesList_4(){
        $data = new SearchResult();
        $data->number_of_products = 50;
        $_GET['search_offset'] = 234;
        $subject = new TemplateFooter(1,$data);
        $result = $subject->getPagesList();
  
        $this->assertInternalType('array',$result);
        $this->assertCount(1,$result);
        $this->assertEquals(1, $result[0]->number);
        $this->assertEquals(0, $result[0]->offset);
        $this->assertEquals(false, $result[0]->marked);
   
    }
   
   
       function testGetPagesList_5(){
        settings::setLocal('max_search_results',33, $this->wid);
        $data = new SearchResult();
        $data->number_of_products = 100;
        $_GET['search_offset'] = 45;
        $subject = new TemplateFooter(1,$data);
        $result = $subject->getPagesList();
  
        $this->assertInternalType('array',$result);
        $this->assertCount(4,$result);
        $this->assertEquals(1, $result[0]->number);
        $this->assertEquals(0, $result[0]->offset);
        $this->assertEquals(false, $result[0]->marked);
        
        $this->assertEquals(2, $result[1]->number);
        $this->assertEquals(33, $result[1]->offset);
       // $this->assertEquals($subject->getFrom()-1, $result[1]->offset); // Weird, i know. and BM. But this should be always true
        $this->assertEquals(true, $result[1]->marked);
        
 
       
       
        settings::setLocal('max_search_results',$this->test_max_prod, $this->wid);
    }
   
   
       function testGetPagesList_6(){
         settings::setLocal('max_search_results',5, $this->wid);
        $data = new SearchResult();
        $data->number_of_products = 18;
        $_GET['search_offset'] = 5;
        $subject = new TemplateFooter(1,$data);
        $result = $subject->getPagesList();
  
        $this->assertInternalType('array',$result);
        $this->assertCount(4,$result);
        $this->assertEquals(1, $result[0]->number);
        $this->assertEquals(0, $result[0]->offset);
        $this->assertEquals(false, $result[0]->marked);
        
        $this->assertEquals(2, $result[1]->number);
        $this->assertEquals(5, $result[1]->offset);
       // $this->assertEquals($subject->getFrom()-1, $result[1]->offset); // Weird, i know. and BM. But this should be always true
        $this->assertEquals(true, $result[1]->marked);
        
        $this->assertEquals(3, $result[2]->number);
        $this->assertEquals(10, $result[2]->offset);
       // $this->assertEquals($subject->getFrom()-1, $result[1]->offset); // Weird, i know. and BM. But this should be always true
        $this->assertEquals(false, $result[2]->marked);
        
        $this->assertEquals(4, $result[3]->number);
        $this->assertEquals(15, $result[3]->offset);
       // $this->assertEquals($subject->getFrom()-1, $result[1]->offset); // Weird, i know. and BM. But this should be always true
        $this->assertEquals(false, $result[3]->marked);
 
       
       
        settings::setLocal('max_search_results',$this->test_max_prod, $this->wid);
    }
   
    function tearDown() {
         $this->initial = settings::setLocal('max_search_results',$this->initial, $this->wid);
            
          $this->dropDatabases();
    }
    

    
    
}


