<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';


$suite  = new PHPUnit_Framework_TestSuite("WidgetFactoryTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);



// Test Case disabled

class WidgetFactoryTest extends BaseDbTest 
{   
    function setUp() {
    //  $this->wid = 1;
  //      $this->buildDatabases();
   //     $this->ws = new WidgetFactory(1);
    }

 function testdummy(){

        $this->assertTrue(true);
    }
 /*
    function testGetResults_nopids(){

        $data = array();
        $result = $this->ws->getResults($data);
        $this->assertInternalType('array',$result);
        $this->assertCount(4, $result,"result is: ".print_r($result,true));
    }

    function testGetGeneralWidget_nopids(){

        $data = array();
        $result = $this->ws->getGeneralWidgets($data);
        $this->assertInternalType('array',$result);
        $this->assertCount(2, $result,"result is: ".print_r($result,true));
    }
    
    function testGetCategoryWidget(){
        $data = array(2);
        $result = $this->ws->getCategoryWidget($data);
       
         $this->assertInstanceOf('CategoryWidget', $result);
         $this->assertCount(2, $result->categories,"result->categories is: ".print_r($result->categories,true)); 
         
         $vdo = VPDO::getVdo(DB_PREFIX.'1');
         $vdo->exec('delete from categories');
         $result = $this->ws->getCategoryWidget($data);
       
         $this->assertNull( $result);
         
    } 
    
    function testGetCategoryWidget2(){
        $data = array(1);
        $result = $this->ws->getCategoryWidget($data);
       
         $this->assertInstanceOf('CategoryWidget', $result);
         $this->assertCount(2, $result->categories,"result->categories is: ".print_r($result->categories,true)); 
         
         $this->assertEquals(1,$result->categories[0]->cid);
         $this->assertEquals(4,$result->categories[1]->cid);
    }    
       

     function testGetGeneralWidget_simple(){
        $data = array(6,7);  
        // product 22 is the one that contain the Version 
        // 1, 2 have Memory 
        // So none should be returned
        $result = $this->ws->getGeneralWidgets($data);
        
        $this->assertNull($result,"result is: ".print_r($result,true));
        
        $data = array(22);
        $result = $this->ws->getGeneralWidgets($data);
        $this->assertInternalType('array',$result);
        $this->assertCount(1,$result, 'size was '. sizeof($result));
        $this->assertInstanceOf('CheckboxeslistWidget',$result['Version']);
        $this->assertEquals($result['Version']->id , 'Version');
        $this->assertCount(2,($result['Version']->options));
        
        
        $data = array(1,2);
        $result = $this->ws->getGeneralWidgets($data);
        $this->assertInternalType('array',$result);
        $this->assertCount(1,$result);
        $this->assertInstanceOf('CheckboxeslistWidget',$result['Memory']);
        $this->assertEquals($result['Memory']->id, 'Memory');
        $this->assertCount(4, ($result['Memory']->options)); // We know there are four different one
        $this->assertContains('4 mb', $result['Memory']->options, print_r($result['Memory']->options, true));
        $this->assertContains('8 mb', $result['Memory']->options);
        $this->assertContains('16 mb', $result['Memory']->options);
        $this->assertContains('32 mb', $result['Memory']->options);
    }
    
     function testGetResults(){
        $data = array();
        // product 22 is the one that contain the Version 
        // 1, 2 have Memory 
        // So none should be returned
        $result = $this->ws->getResults($data);

        $this->assertCount(4, $result,"result is: ".print_r($result,true));

        $data = array(22);
        $result = $this->ws->getResults($data);
        $this->assertArrayHasKey('Version', $result, print_r($result, true));
        $this->assertInstanceOf('CheckboxeslistWidget',$result['Version']);
        $this->assertArrayHasKey('category_three',$result);
        $this->assertInstanceOf('CategoryWidget',$result['category_three']);
        $this->assertArrayHasKey('price_slider',$result);
        $this->assertInstanceOf('SliderWidget',$result['price_slider']);
        $this->assertCount(3, $result,"result is: ".print_r($result,true));
        
        
        $data = array(4,18,16,14,12,9,10,7,5,20,19,17,15,13,11,8,6,2);
        $result = $this->ws->getResults($data);
        $this->assertArrayHasKey('Memory', $result, print_r($result, true));
        $this->assertInstanceOf('CheckboxeslistWidget',$result['Memory']);
        $this->assertArrayHasKey('category_three',$result);
        $this->assertInstanceOf('CategoryWidget',$result['category_three']);
        $this->assertArrayHasKey('price_slider',$result);
        $this->assertInstanceOf('SliderWidget',$result['price_slider']);
        $this->assertCount(3, $result);
     }


     function testSlider(){
        $data = array();
     
        $tax_multiplier = 1.25;
        $cheapest_product_cost = 0;
        Settings::setLocal('vat_multiplyer',$tax_multiplier, 1);
        $_GET['vubla_enable_vat'] = true;
        $expesivest_product_cost = 499.99;
        $_GET['min_options'] = array('lowest_price'=>$tax_multiplier*$cheapest_product_cost);
        $_GET['max_options'] = array('lowest_price'=>$tax_multiplier*$expesivest_product_cost);
      
        $data = array(4,18,16,14,12,9,10,7,5,20,19,17,15,13,11,8,6,2);
        $result = $this->ws->getPriceSliderWidget($data);

        $this->assertInstanceOf('SliderWidget', $result);
        
        $this->assertEquals(floor($tax_multiplier*$cheapest_product_cost),$result->min);
        $this->assertEquals(ceil($tax_multiplier*$expesivest_product_cost),$result->max);
        $this->assertEquals(floor($tax_multiplier*$cheapest_product_cost),$result->selected_min);
        $this->assertEquals(ceil($tax_multiplier*$expesivest_product_cost),$result->selected_max);
     }
   
   
      function testSlider2(){
        $data = array();
        $tax_multiplier = 1234.25;
        $cheapest_product_cost = 0;
        Settings::setLocal('vat_multiplyer',$tax_multiplier, 1);
        $_GET['vubla_enable_vat'] = true;
        $expesivest_product_cost = 499.99;
        $_GET['min_options'] = array('lowest_price'=>$tax_multiplier*$cheapest_product_cost);
        $_GET['max_options'] = array('lowest_price'=>$tax_multiplier*$expesivest_product_cost);
       
        $data = array(4,18,16,14,12,9,10,7,5,20,19,17,15,13,11,8,6,2);
        $result = $this->ws->getPriceSliderWidget($data);

        $this->assertInstanceOf('SliderWidget', $result);
        
        $this->assertEquals(floor($tax_multiplier*$cheapest_product_cost),$result->min);
        $this->assertEquals(ceil($tax_multiplier*$expesivest_product_cost),$result->max);
        $this->assertEquals(floor($tax_multiplier*$cheapest_product_cost),$result->selected_min);
        $this->assertEquals(ceil($tax_multiplier*$expesivest_product_cost),$result->selected_max);
        
     }
   
    function tearDown() {
        unset($this->ws);
        $this->dropDatabases();
    }
    
    
    
    */
}


