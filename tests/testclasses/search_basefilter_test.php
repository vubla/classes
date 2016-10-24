<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';


$suite  = new PHPUnit_Framework_TestSuite("BaseFilterTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);


/**
 * 
 */
class MockedBaseFilter extends BaseFilter {
	
	function __construct() {
		parent::__construct(1);
	}
    
    protected function filter(array $product_ids){
        return $product_ids;
    }
    
    function callIsOption($option)
    {
        return $this->isOption($option);
    }
    
    function callMaintainOrder($ordered,$unordered)
    {
        return $this->maintainOrder($ordered,$unordered);
    }
}




class BaseFilterTest extends BaseDbTest 
{
    function setUp() {
      
        $this->buildDatabases(); 
        $this->filter = new MockedBaseFilter();
    }

    function tearDown() {
        unset($this->filter);
        $this->dropDatabases();
    }
    
    function testThatNoFilterWorks()
    {
        $data = array(1,2,3,4,5,6,7,8,9);
        $expected =  $data;
        $result = $this->filter->getResults($data);
        $this->assertInternalType('array',$result);
        $this->assertEquals($expected, $result);
    }

    function testMaintainOrder()
    {
        $ordered = array(1,2,3,4,5,6,7,8,9,10);
        $unordered = array(6,8,2,4);
        $expected = array(2,4,6,8);
        $result = $this->filter->callMaintainOrder($ordered,$unordered);
        $this->assertInternalType('array',$result);
        $this->assertEquals($expected,$result);
    }
    
    function testMaintainOrder2()
    {
        $ordered = array(1,3,4,5,6,7,8,9,10,2);
        $unordered = array(6,8,2,4);
        $expected = array(4,6,8,2);
        $result = $this->filter->callMaintainOrder($ordered,$unordered);
        $this->assertInternalType('array',$result);
        $this->assertEquals($expected,$result);
    }
    
    function testMaintainOrderEmpty1()
    {
        $ordered = array(1,3,4,5,6,7,8,9,10,2);
        $unordered = array();
        $expected = array();
        $result = $this->filter->callMaintainOrder($ordered,$unordered);
        $this->assertInternalType('array',$result);
        $this->assertEquals($expected,$result);
    }
    
    function testMaintainThrowTest()
    {
        $ordered = array();
        $unordered = array(6,8,2,4);
        try
        {
            $result = $this->filter->callMaintainOrder($ordered,$unordered);
            $this->assertFail('No exception throw, result variable is: '.$result);
        }
        catch(VublaException $e)
        {}
    }
    
    function testIsOptionSucces()
    {
        $res = $this->filter->callIsOption($this->filter->getLowestPriceString());
        $this->assertTrue($res);
    }
    
    function testIsOptionSucces2()
    {
        $res = $this->filter->callIsOption('discount_price');
        $this->assertTrue($res);
    }
    
    function testIsOptionSuccesCase()
    {
        $res = $this->filter->callIsOption('model'); //Case?
        $this->assertTrue($res);
    }
    
    
    function testIsOptionFailure()
    {
        $res = $this->filter->callIsOption('name');
        $this->assertFalse($res);
    }
}

?>