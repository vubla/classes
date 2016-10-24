<?php


require_once '../vublamailer.php';
require_once '../basedbtest.php';
$suite  = new PHPUnit_Framework_TestSuite("StringfilterTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);


class FastStringfilterTest extends BaseDbTest 
{
    function setUp() {
        //$this->resetDatabases();
        $this->buildDatabases(); 
        $this->data = 'dvd';
        $this->searcher = new FastStringFilter(1,$this->data,true);
    }

    function tearDown() {
        unset($this->searcher);
        $this->dropDatabases();
        
    }
    
    ##################
    // GetResult
    ##################

    function testGetCorrectNumberFromGetResults() 
    {
    	Settings::setLocal('suggestions','0',1);
        $ref = array();
        $expected = array (5,19,10,15,6,20,11,16,7,12,17,8,13,4,18,9,14);
        $result = $this->searcher->getResults($ref);
        $this->assertCount(sizeof($expected),$result);
        foreach ($expected as $item) 
        {
            $this->assertContains($item,$result);
        }
    }
	
    function testGetSomethingFromGetResults() 
    {
        $ref = array();
        $result = $this->searcher->getResults($ref);
        $this->assertInternalType('array',$result);
        foreach ($result as $item) 
        {
            $this->assertInternalType('int',$item);
        }
    }
    
    function testProductIdInputGetResultsSucces() 
    {
        $searcher = new FastStringFilter(1,$this->data,false);
        $ref = array(5,19,10);
        $expected = array (5,19,10);
        $result = $searcher->getResults($ref);
        $this->assertCount(sizeof($expected),$result);
        foreach ($expected as $item) {
            $this->assertContains($item,$result);
        }
    }
    
    function testProductIdInputGetResultsFailure() 
    {
        $searcher = new FastStringFilter(1,$this->data,false);
        $ref = array(1,3,21);
        $expected = array ();
        $result = $searcher->getResults($ref);
        $this->assertCount(sizeof($expected),$result,"result: ".print_r($result,true));
        foreach ($expected as $item) { // yeah i know
            $this->assertContains($item,$result);
        }
    }
    
    function testProductIdInputGetResultsMix() 
    {
        $searcher = new FastStringFilter(1,$this->data,false);
        $ref = array(5,3,21,1,19,10);
        $expected = array (5,19,10);
        $result = $searcher->getResults($ref);
        $this->assertCount(sizeof($expected),$result);
        foreach ($expected as $item) {
            $this->assertContains($item,$result);
        }
    }
    
   function testGetNothingWhenNothingShouldBeFound() 
   {
        $ref = array();
        $expected = array ();
        $searcher = new FastStringFilter(1,'sadfghdjfkdgsaghsjdkhgfdfghdkjdhgfdasghjkfgfdas',true);
        $result = $searcher->getResults($ref);
        $this->assertCount(sizeof($expected),$result);
       
    }
    
    function testGetResultsMissingEnd() 
    {
        $ref = 'matri';
        $expected = array(6);
        $searcher = new FastStringFilter(1,$ref,true);
        $result = $searcher->getResults(array());
        $this->assertEquals($expected,$result);
    }

    function testFindEverything() 
    {
    	Settings::setLocal('suggestions','0',1);
        $ref = array();
       
        $searcher = new FastStringFilter(1,'',true);
        $result = $searcher->getResults($ref);
        $this->assertCount(28,$result,$searcher->query());
        foreach ($result as $item) {
            $this->assertInternalType('int',$item);
        }
    }
	/*
	function testFindSomethingOnTwoWords() 
	{
		Settings::setLocal('suggestions','0',WID);
        $ref = array();
        $expected = array(1);
        $searcher = new FastStringFilter('dvd marry',true);
        $result = $searcher->getResults($ref);
        $this->assertCount(sizeof($expected),$result);
        foreach ($expected as $item) {
            $this->assertContains($item,$result);
        }
	}*/
}