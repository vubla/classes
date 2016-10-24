<?php


require '../basedbtest.php';


$suite  = new PHPUnit_Framework_TestSuite("TestTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);





class TestTest extends BaseDbTest 
{
    function setUp() {      
        
    }

    function tearDown() {
        
    }
    
    function testtest(){
        $this->assertTrue(true);
    }
}

