<?php
require_once '../vublamailer.php';
require_once '../basedbtest.php';
 


$suite  = new PHPUnit_Framework_TestSuite("SortingFilterTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);





class SortingFilterTest extends BaseDbTest 
{
    function setUp() {
        //$this->resetDatabases();
        $this->buildDatabases();
        $this->sorter = new SortingFilter(1);
        $this->data = array(12,11,2,14,17,15,13,18,8,20,5,16,6,4,19,9,10,7);
        $this->size = sizeof($this->data);
    }

    function tearDown() {
        unset($this->sorter);
        $this->dropDatabases();
    } 
    
    function testGetResultsWithNoSorter() {
        
        $result = $this->sorter->getResults($this->data);
        $this->assertInternalType('array',$result);
        foreach ($result as $item) {
            $this->assertInternalType('int',$item);
        }
//SELECT concat(concat('$this->assertEquals(',product_id),',$result);') FROM product_options where product_id = 4 or product_id =18 or product_id =16 or product_id =14 or product_id =12 or product_id =9 or product_id =10 or product_id =7 or product_id =5 or product_id =20 or product_id =19 or product_id =17 or product_id =15 or product_id =13 or product_id =11 or product_id =8 or product_id =6 or product_id =2 ORDER BY `product_options`.`name` ASC
        $this->assertCount($this->size,$result);
        $this->assertEquals($this->data,$result);
    }  
    
    function testGetASCsortByNameFromGetResults() {
        $this->sorter->setSortBy('name'); 
        $this->sorter->setSortOrder('asc'); 
        
        $result = $this->sorter->getResults($this->data);
        $this->assertInternalType('array',$result);
        foreach ($result as $item) {
            $this->assertInternalType('int',$item);
        }
//SELECT concat(concat('$this->assertEquals(',product_id),',$result);') FROM product_options where product_id = 4 or product_id =18 or product_id =16 or product_id =14 or product_id =12 or product_id =9 or product_id =10 or product_id =7 or product_id =5 or product_id =20 or product_id =19 or product_id =17 or product_id =15 or product_id =13 or product_id =11 or product_id =8 or product_id =6 or product_id =2 ORDER BY `product_options`.`name` ASC
        $this->assertCount($this->size,$result);
        $this->assertEquals(8,$result[0]);
        $this->assertEquals(20,$result[1]);
        $this->assertEquals(5,$result[2]);
        $this->assertEquals(16,$result[3]);
        $this->assertEquals(12,$result[4]);
        $this->assertEquals(11,$result[5]);
        $this->assertEquals(15,$result[6]);
        $this->assertEquals(13,$result[7]);
        $this->assertEquals(2,$result[8]);
        $this->assertEquals(14,$result[9]);
        $this->assertEquals(17,$result[10]);
        $this->assertEquals(18,$result[11]);
        $this->assertEquals(6,$result[12]);
        $this->assertEquals(4,$result[13]);
        $this->assertEquals(19,$result[14]);
        $this->assertEquals(9,$result[15]);
        $this->assertEquals(10,$result[16]);
        $this->assertEquals(7,$result[17]);
    }  

    function testGetDESCsortByNameFromGetResults() {
        $this->sorter->setSortBy('name'); 
        $this->sorter->setSortOrder('desc'); 
        
        $result = $this->sorter->getResults($this->data);
        $this->assertInternalType('array',$result);
        foreach ($result as $item) {
            $this->assertInternalType('int',$item);
        }
   
//SELECT concat(concat('$this->assertEquals(',product_id),',$result);') FROM product_options where product_id = 4 or product_id =18 or product_id =16 or product_id =14 or product_id =12 or product_id =9 or product_id =10 or product_id =7 or product_id =5 or product_id =20 or product_id =19 or product_id =17 or product_id =15 or product_id =13 or product_id =11 or product_id =8 or product_id =6 or product_id =2 ORDER BY `product_options`.`name` DESC
        $this->assertCount($this->size,$result);
        $this->assertEquals(7,$result[0]);
        $this->assertEquals(10,$result[1]);
        $this->assertEquals(9,$result[2]);
        $this->assertEquals(19,$result[3]);
        $this->assertEquals(4,$result[4]);
        $this->assertEquals(6,$result[5]);
        $this->assertEquals(18,$result[6]);
        $this->assertEquals(17,$result[7]);
        $this->assertEquals(14,$result[8]);
        $this->assertEquals(2,$result[9]);
        $this->assertEquals(13,$result[10]);
        $this->assertEquals(15,$result[11]);
        $this->assertEquals(11,$result[12]);
        $this->assertEquals(12,$result[13]);
        $this->assertEquals(16,$result[14]);
        $this->assertEquals(5,$result[15]);
        $this->assertEquals(20,$result[16]);
        $this->assertEquals(8,$result[17]);
     }

    function testGetASCsortByPriceFromGetResults() {
        $this->sorter->setSortBy('lowest_price'); 
        $this->sorter->setSortOrder('asc'); 
        
        $result = $this->sorter->getResults($this->data);
        $this->assertInternalType('array',$result);
        foreach ($result as $item) {
            $this->assertInternalType('int',$item);
        }
//SELECT concat(concat('$this->assertEquals(',product_id),',$result);') FROM product_options where product_id = 4 or product_id =18 or product_id =16 or product_id =14 or product_id =12 or product_id =9 or product_id =10 or product_id =7 or product_id =5 or product_id =20 or product_id =19 or product_id =17 or product_id =15 or product_id =13 or product_id =11 or product_id =8 or product_id =6 or product_id =2 ORDER BY `product_options`.`name` ASC
        $this->assertCount($this->size,$result);
        $result = array_reverse($result); // I know, I'm lazy
        $this->assertCount($this->size,$result);
        $this->assertEquals(2,$result[0]);
        $this->assertEquals(20,$result[1]);
        $this->assertEquals(19,$result[2]);
        
        $temp = array_slice($result, 3,2);
        $this->assertContains(18,$temp);
        $this->assertContains(4,$temp);
        
        $temp = array_slice($result, 5,2);
        $this->assertContains(17,$temp);
        $this->assertContains(12,$temp);
        
        
        $this->assertEquals(8,$result[7]);
        $this->assertEquals(15,$result[8]);
        
        $temp = array_slice($result, 9,2);
        $this->assertContains(13,$temp);
        $this->assertContains(7,$temp);
        
        $this->assertEquals(14,$result[11]);
        
        $temp = array_slice($result, 12,2);
        $this->assertContains(5,$temp);
        $this->assertContains(6,$temp);
        
        $temp = array_slice($result, 14,4);
        $this->assertContains(16,$temp);
        $this->assertContains(9,$temp);
        $this->assertContains(10,$temp);
        $this->assertContains(11,$temp);
    }
    

    function testGetDESCsortByPriceFromGetResults() {
        $this->sorter->setSortBy('lowest_price'); 
        $this->sorter->setSortOrder('desc'); 
        
        $result = $this->sorter->getResults($this->data);
        //var_dump($result); exit;
        $this->assertInternalType('array',$result);
        foreach ($result as $item) {
            $this->assertInternalType('int',$item);
        }
/*select concat(concat('$this->assertEquals(',product_id),',$result);') From (SELECT `options_values`.`product_id` AS `product_id`, ((case when (`temp_table_0`.`max_discount_price` IS NULL) then CAST(`temp_table_0`.`max_products_price` AS decimal(20,2)) else CAST(`temp_table_0`.`max_discount_price` AS decimal(20,2)) end)) AS `max_johnson` FROM `options` 
        JOIN `options_values` on (`options`.`id` = `options_values`.`option_id`) JOIN (SELECT `options_values`.`product_id` AS `product_id`, max((case when (`options`.`name` = 'discount_price') then CAST(`options_values`.`name` AS decimal(20,2)) else NULL end)) AS `max_discount_price`, max((case when (`options`.`name` = 'products_price') then CAST(`options_values`.`name` AS decimal(20,2)) else NULL end)) AS `max_products_price` FROM `options` 
        JOIN `options_values` on (`options`.`id` = `options_values`.`option_id`) group by `options_values`.`product_id`) AS temp_table_0 on `temp_table_0`.`product_id` = `options_values`.`product_id`  group by `options_values`.`product_id`) as s  where product_id = 4 or product_id =18 or product_id =16 or product_id =14 or product_id =12 or product_id =9 or product_id =10 or product_id =7 or product_id =5 or product_id =20 or product_id =19 or product_id =17 or product_id =15 or product_id =13 or product_id =11 or product_id =8 or product_id =6 or product_id =2 ORDER BY max_johnson DESC
*/
        $this->assertCount($this->size,$result);
        $this->assertEquals(2,$result[0]);
        $this->assertEquals(20,$result[1]);
        $this->assertEquals(19,$result[2]);
        
        $temp = array_slice($result, 3,2);
        $this->assertContains(18,$temp);
        $this->assertContains(4,$temp);
        
        $temp = array_slice($result, 5,2);
        $this->assertContains(17,$temp);
        $this->assertContains(12,$temp);
        
        
        $this->assertEquals(8,$result[7]);
        $this->assertEquals(15,$result[8]);
        
        $temp = array_slice($result, 9,2);
        $this->assertContains(13,$temp);
        $this->assertContains(7,$temp);
        
        $this->assertEquals(14,$result[11]);
        
        $temp = array_slice($result, 12,2);
        $this->assertContains(5,$temp);
        $this->assertContains(6,$temp);
        
        $temp = array_slice($result, 14,4);
        $this->assertContains(16,$temp);
        $this->assertContains(9,$temp);
        $this->assertContains(10,$temp);
        $this->assertContains(11,$temp);
    }

    function testGetAllDESCsortByNameFromGetResults() {
        $sorter = new SortingFilter(1,true);
        $sorter->setSortBy('name'); 
        $sorter->setSortOrder('desc'); 
        
        $result = $sorter->getResults($this->data);
        $this->assertInternalType('array',$result);
        foreach ($result as $item) {
            $this->assertInternalType('int',$item);
        }
   
//SELECT concat(concat('$this->assertEquals(',product_id),',$result);') FROM product_options ORDER BY `product_options`.`name` DESC
        $this->assertCount(28,$result);
        $i = 0;
        $this->assertEquals(7,$result[$i++]);
        $this->assertEquals(22,$result[$i++]);
        $this->assertEquals(10,$result[$i++]);
        $this->assertEquals(9,$result[$i++]);
        $this->assertEquals(19,$result[$i++]);
        $this->assertEquals(23,$result[$i++]);
        $this->assertEquals(4,$result[$i++]);
        $this->assertEquals(6,$result[$i++]);
        $this->assertEquals(21,$result[$i++]);
        $this->assertEquals(18,$result[$i++]);
        $this->assertEquals(17,$result[$i++]);
        $this->assertEquals(28,$result[$i++]);
        $this->assertEquals(14,$result[$i++]);
        $this->assertEquals(25,$result[$i++]);
        $this->assertEquals(3,$result[$i++]);
        $this->assertEquals(26,$result[$i++]);
        $this->assertEquals(2,$result[$i++]);
        $this->assertEquals(1,$result[$i++]);
        $this->assertEquals(13,$result[$i++]);
        $this->assertEquals(27,$result[$i++]);
        $this->assertEquals(15,$result[$i++]);
        $this->assertEquals(11,$result[$i++]);
        $this->assertEquals(24,$result[$i++]);
        $this->assertEquals(12,$result[$i++]);
        $this->assertEquals(16,$result[$i++]);
        $this->assertEquals(5,$result[$i++]);
        $this->assertEquals(20,$result[$i++]);
        $this->assertEquals(8,$result[$i++]);
     }

    function testGetDESCsortByNameFromGetResultsOnNoProductIds() {
        $this->sorter->setSortBy('name'); 
        $this->sorter->setSortOrder('desc'); 
        
        $result = $this->sorter->getResults(array());
        $this->assertInternalType('array',$result);
        $this->assertEmpty($result);
    }
}


