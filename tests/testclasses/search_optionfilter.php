<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';

$suite  = new PHPUnit_Framework_TestSuite("OptionFilterTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);





class OptionFilterTest extends BaseDbTest 
{
    var $wid = 1;
    function setUp() {
      
        $this->buildDatabases(); 
        $this->filter = new OptionFilter(1);
       
      

    }

    function tearDown() {
        unset($this->filter);
        $this->dropDatabases();
        
    }
    
    function testThatNoFilterWorks(){
        $data = array(1,2,3,4,5,6,7,8,9);
        $expected =  $data;
        $result = $this->filter->getResults($data);
        $this->assertInternalType('array',$result);
        $this->assertEquals($expected, $result);
    }
    
   function testThatSomeCategoryIdtest(){
        $data = array(1,2,3,4,5,6,7,8,9);
        $this->filter->setEqOptions(array('category_id'=>array('12','13')));
        $result = $this->filter->getResults($data);
       // $expected = array(8,19,7);'
        $this->assertInternalType('array',$result);
        $this->assertCount(2, $result, 'We got: '. print_r($result,true));
        $this->assertContains(8, $result);
        $this->assertContains(7, $result);
   }
    
    function testThatSomeCategoryNametest(){
        $data = array(1,2,3,4,5,6,7,8,9);
        $this->filter->setEqOptions(array('category'=>array('Cartoons','Comedy')));
        $result = $this->filter->getResults($data);
       // $expected = array(8,19,7);'
        $this->assertInternalType('array',$result);
        $this->assertCount(2, $result, 'We got: '. print_r($result,true));
        $this->assertContains(8, $result);
        $this->assertContains(7, $result);
   }
    
   function testThatSomeCategoryNametest2(){
     
        $data = array(1,2,3,4,5,6,7,8,9,10,11,12,14,15,16,17,18,19);
         $this->filter->setEqOptions(array('category'=>array('Cartoons','Comedy')));
        $result = $this->filter->getResults($data);
       // $expected = array(8,19,7);
        $this->assertInternalType('array',$result);
        $this->assertCount(3, $result);
        $this->assertContains(8, $result);
        $this->assertContains(7, $result);
        $this->assertContains(19, $result);
    }
    
    function testThatSomePricetest3(){
       /*
        * select concat(concat('$this->assertContains(',product_id),',$result);') From (SELECT `options_values`.`product_id` AS `product_id`, ((case when (`temp_table_0`.`max_discount_price` IS NULL) then CAST(`temp_table_0`.`max_products_price` AS decimal(20,2)) else CAST(`temp_table_0`.`max_discount_price` AS decimal(20,2)) end)) AS `max_johnson` FROM `options` 
        JOIN `options_values` on (`options`.`id` = `options_values`.`option_id`) JOIN (SELECT `options_values`.`product_id` AS `product_id`, max((case when (`options`.`name` = 'discount_price') then CAST(`options_values`.`name` AS decimal(20,2)) else NULL end)) AS `max_discount_price`, max((case when (`options`.`name` = 'products_price') then CAST(`options_values`.`name` AS decimal(20,2)) else NULL end)) AS `max_products_price` FROM `options` 
        JOIN `options_values` on (`options`.`id` = `options_values`.`option_id`) group by `options_values`.`product_id`) AS temp_table_0 on `temp_table_0`.`product_id` = `options_values`.`product_id`  group by `options_values`.`product_id`) as s  where (product_id = 4 or product_id =18 or product_id =16 or product_id =14 or product_id =12 or product_id =9 or product_id =10 or product_id =7 or product_id =5 or product_id =20 or product_id =19 or product_id =17 or product_id =15 or product_id =13 or product_id =11 or product_id =8 or product_id =6 or product_id =2) and max_johnson > 40 ORDER BY max_johnson DESC
        * 
        */
        $data = array(4 ,18 ,16 ,14 ,12 ,9 ,10, 7 ,5 ,20 ,19 ,17, 15 ,13 ,11 ,8 ,6 ,2);
        $filter = new OptionFilter(1);
        $filter->setMinOptions(array('lowest_price'=>40));
        $result = $filter->getResults($data);
       // $expected = array(8,19,7);
        $this->assertInternalType('array',$result);
        $this->assertCount(5, $result);
        $this->assertContains(2,$result);
        $this->assertContains(20,$result);
        $this->assertContains(19,$result);
        $this->assertContains(18,$result);
        $this->assertContains(4,$result);
    }
    
        function testThatSomePricetest4(){
       /*
        * select concat(concat('$this->assertContains(',product_id),',$result);') From (SELECT `options_values`.`product_id` AS `product_id`, ((case when (`temp_table_0`.`max_discount_price` IS NULL) then CAST(`temp_table_0`.`max_products_price` AS decimal(20,2)) else CAST(`temp_table_0`.`max_discount_price` AS decimal(20,2)) end)) AS `max_johnson` FROM `options` 
        JOIN `options_values` on (`options`.`id` = `options_values`.`option_id`) JOIN (SELECT `options_values`.`product_id` AS `product_id`, max((case when (`options`.`name` = 'discount_price') then CAST(`options_values`.`name` AS decimal(20,2)) else NULL end)) AS `max_discount_price`, max((case when (`options`.`name` = 'products_price') then CAST(`options_values`.`name` AS decimal(20,2)) else NULL end)) AS `max_products_price` FROM `options` 
        JOIN `options_values` on (`options`.`id` = `options_values`.`option_id`) group by `options_values`.`product_id`) AS temp_table_0 on `temp_table_0`.`product_id` = `options_values`.`product_id`  group by `options_values`.`product_id`) as s  where (product_id = 4 or product_id =18 or product_id =16 or product_id =14 or product_id =12 or product_id =9 or product_id =10 or product_id =7 or product_id =5 or product_id =20 or product_id =19 or product_id =17 or product_id =15 or product_id =13 or product_id =11 or product_id =8 or product_id =6 or product_id =2) and max_johnson > 40 ORDER BY max_johnson DESC
        * 
        */
        $data = array(4 ,18 ,16 ,14 ,12 ,9 ,10, 7 ,5 ,20 ,19 ,17, 15 ,13 ,11 ,8 ,6 ,2);
        $filter = new OptionFilter(1);
        $filter->setMaxOptions(array('lowest_price'=>40));
        $result = $filter->getResults($data);
       
        $this->assertInternalType('array',$result);
        $this->assertCount(13, $result);
        $this->assertContains(17,$result);
        $this->assertContains(12,$result);
        $this->assertContains(8,$result);
        $this->assertContains(15,$result);
        $this->assertContains(13,$result);
        $this->assertContains(7,$result);
        $this->assertContains(14,$result);
        $this->assertContains(5,$result);
        $this->assertContains(6,$result);
        $this->assertContains(16,$result);
        $this->assertContains(9,$result);
        $this->assertContains(10,$result);
        $this->assertContains(11,$result);
    }


       function testCombinationtest(){
       /*
        * select concat(concat('$this->assertContains(',product_id),',$result);') From (SELECT `options_values`.`product_id` AS `product_id`, ((case when (`temp_table_0`.`max_discount_price` IS NULL) then CAST(`temp_table_0`.`max_products_price` AS decimal(20,2)) else CAST(`temp_table_0`.`max_discount_price` AS decimal(20,2)) end)) AS `max_johnson` FROM `options` 
        JOIN `options_values` on (`options`.`id` = `options_values`.`option_id`) JOIN (SELECT `options_values`.`product_id` AS `product_id`, max((case when (`options`.`name` = 'discount_price') then CAST(`options_values`.`name` AS decimal(20,2)) else NULL end)) AS `max_discount_price`, max((case when (`options`.`name` = 'products_price') then CAST(`options_values`.`name` AS decimal(20,2)) else NULL end)) AS `max_products_price` FROM `options` 
        JOIN `options_values` on (`options`.`id` = `options_values`.`option_id`) group by `options_values`.`product_id`) AS temp_table_0 on `temp_table_0`.`product_id` = `options_values`.`product_id`  group by `options_values`.`product_id`) as s  where (product_id = 4 or product_id =18 or product_id =16 or product_id =14 or product_id =12 or product_id =9 or product_id =10 or product_id =7 or product_id =5 or product_id =20 or product_id =19 or product_id =17 or product_id =15 or product_id =13 or product_id =11 or product_id =8 or product_id =6 or product_id =2) and max_johnson > 40 ORDER BY max_johnson DESC
        * 
        */
        $data = array(4 ,18 ,16 ,14 ,12 ,9 ,10, 7 ,5 ,20 ,19 ,17, 15 ,13 ,11 ,8 ,6 ,2);
        $filter = new OptionFilter(1);
        $filter->setMaxOptions(array('lowest_price'=>40));
 
        $filter->setEqOptions(array('category'=>array('Cartoons','Comedy')));
        $result = $filter->getResults($data);
       // $expected = array(8,19,7);
        $this->assertInternalType('array',$result);
        $this->assertCount(2, $result);
   
        $this->assertContains(8,$result);
        $this->assertContains(7,$result);
    
    }

   function testCombinationtest2(){
       /*
        * select concat(concat('$this->assertContains(',product_id),',$result);') From (SELECT `options_values`.`product_id` AS `product_id`, ((case when (`temp_table_0`.`max_discount_price` IS NULL) then CAST(`temp_table_0`.`max_products_price` AS decimal(20,2)) else CAST(`temp_table_0`.`max_discount_price` AS decimal(20,2)) end)) AS `max_johnson` FROM `options` 
        JOIN `options_values` on (`options`.`id` = `options_values`.`option_id`) JOIN (SELECT `options_values`.`product_id` AS `product_id`, max((case when (`options`.`name` = 'discount_price') then CAST(`options_values`.`name` AS decimal(20,2)) else NULL end)) AS `max_discount_price`, max((case when (`options`.`name` = 'products_price') then CAST(`options_values`.`name` AS decimal(20,2)) else NULL end)) AS `max_products_price` FROM `options` 
        JOIN `options_values` on (`options`.`id` = `options_values`.`option_id`) group by `options_values`.`product_id`) AS temp_table_0 on `temp_table_0`.`product_id` = `options_values`.`product_id`  group by `options_values`.`product_id`) as s  where (product_id = 4 or product_id =18 or product_id =16 or product_id =14 or product_id =12 or product_id =9 or product_id =10 or product_id =7 or product_id =5 or product_id =20 or product_id =19 or product_id =17 or product_id =15 or product_id =13 or product_id =11 or product_id =8 or product_id =6 or product_id =2) and max_johnson > 40 ORDER BY max_johnson DESC
        * 
        */
        $data = array(4 ,18 ,16 ,14 ,12 ,9 ,10, 7 ,5 ,20 ,19 ,17, 15 ,13 ,11 ,8 ,6 ,2);
        $filter = new OptionFilter(1);
        $filter->setMaxOptions(array('lowest_price'=>40));
         $filter->setMinOptions(array('lowest_price'=>35));
        $filter->setEqOptions(array('category'=>array('Cartoons','Comedy')));
        $result = $filter->getResults($data);
       // $expected = array(8,19,7);
        $this->assertInternalType('array',$result);
        $this->assertCount(1, $result);
   
        $this->assertContains(8,$result);
    
    }



    function testCorrectOrder() {
        $this->data = array(1,2,3,4,5,6,7,8,9);
        $data = array_reverse($this->data);
        $filter = new OptionFilter(1);
        $res = $filter->getResults($data);
        
        $this->assertEquals(sizeof($data),sizeof($res));
        for($i = 0 ; $i < sizeof($res) ;$i++){
            $this->assertEquals($data[$i],$res[$i]);
        }
    }
    
    function testCorrectOrder2() {
        $this->data = array(1,2,3,4,5,6,7,8,9);
        $data = ($this->data);
        $filter = new OptionFilter(1);
        $res = $filter->getResults($data);
        
        $this->assertEquals(sizeof($data),sizeof($res));
        for($i = 0 ; $i < sizeof($res) ;$i++){
            $this->assertEquals($data[$i],$res[$i]);
        }
    }
    
   
 
}

