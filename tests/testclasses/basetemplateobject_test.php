<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';

$suite  = new PHPUnit_Framework_TestSuite("BaseTemplateObjectTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);





class BaseTemplateObjectTest extends BaseDbTest 
{
    function setUp() {
    }
    
    function tearDown() {
        $this->wid = 1;
    }

    function testGetShopLink(){
       $bto = new BaseTemplateObject(1,new SearchResult());
       $_GET['host'] = 'oscommerce.ratow.dk';
       $_GET['file'] = 'advanced_search_result.php';
       $_GET['param'] = 'q';
       $_GET['q'] = 'dvd';
       $arr['category'] = '2';
       $arr['sortby'] = '3';
       $_GET['postvar'] = ($arr);
       $result = $bto->getShopLink();
       $expected = 'http://oscommerce.ratow.dk/advanced_search_result.php?q=dvd&category=2&sortby=3';
       $this->assertEquals($expected, $result);

       $bto = new BaseTemplateObject(1,new SearchResult());
       $_GET['host'] = 'oscommerce.ratow.dk';
       $_GET['file'] = 'advanced_search_result.php';
       $_GET['param'] = 'q';
       $_GET['q'] = 'dvd';
       $arr['category'] = '2';
       $arr['sortby'] = '3';
       $_GET['postvar'] = null;
       $_GET['getvar'] = ($arr);
       $result = $bto->getShopLink(null,'oscommerce.joakimbertil.com');
       $expected = 'http://oscommerce.joakimbertil.com/advanced_search_result.php?q=dvd&category=2&sortby=3';
       $this->assertEquals($expected, $result);
       
       $bto = new BaseTemplateObject(1,new SearchResult());
       $_GET['host'] = 'oscommerce.ratow.dk';
       $_GET['file'] = 'advanced_search_result.php';
       $_GET['param'] = 'q';
       $_GET['q'] = 'dvd';
       $arr['category'] = '2';
       $arr['sortby'] = '3';
       $_GET['postvar'] = null;
       $_GET['getvar'] = null;
       $result = $bto->getShopLink(array('search_offset'=> 1),'oscommerce.joakimbertil.com');
       $expected = 'http://oscommerce.joakimbertil.com/advanced_search_result.php?q=dvd&search_offset=1';
       $this->assertEquals($expected, $result);
       
        $bto = new BaseTemplateObject(1,new SearchResult());
       $_GET['host'] = 'oscommerce.ratow.dk';
       $_GET['file'] = 'advanced_search_result.php';
       $_GET['param'] = 'q';
       $_GET['q'] = 'dvd';
       $arr['category'] = '2';
       $arr['sortby'] = '3';
       $_GET['postvar'] = null;
       $_GET['getvar'] = $arr;
       $result = $bto->getShopLink(array('search_offset'=> 1),'oscommerce.joakimbertil.com');
       $expected = 'http://oscommerce.joakimbertil.com/advanced_search_result.php?q=dvd&category=2&sortby=3&search_offset=1';
       $this->assertEquals($expected, $result);
    }
    
    function testfindOverlapWithSmallOverlap() {
    	$bto = new BaseTemplateObject(1,new SearchResult());
		$in1 = 'abcde';
		$in2 = 'cdefg';
		$expected = 'cde';
		$res = $bto->findOverlap($in1,$in2);
		$this->assertEquals($expected,$res);
    }
    
    function testfindOverlapWithLargeOverlap() {
    	$bto = new BaseTemplateObject(1,new SearchResult());
		$in1 = 'a123123abc';
		$in2 = '123abc444';
		$expected = '123abc';
		$res = $bto->findOverlap($in1,$in2);
		$this->assertEquals($expected,$res);
    }
    
    function testfindOverlapWithNoOverlap() {
    	$bto = new BaseTemplateObject(1,new SearchResult());
		$in1 = 'abc';
		$in2 = '123';
		$expected = '';
		$res = $bto->findOverlap($in1,$in2);
		$this->assertEquals($expected,$res);
    }
    
    
    function testreplaceOverlapWithSmallOverlap() {
    	$bto = new BaseTemplateObject(1,new SearchResult());
		$in1 = 'abcde';
		$in2 = 'cdefg';
		$expected = 'abcdefg';
		$res = $bto->replaceOverlap($in1,$in2);
		$this->assertEquals($expected,$res);
    }
    
    function testreplaceOverlapWithLargeOverlap() {
    	$bto = new BaseTemplateObject(1,new SearchResult());
		$in1 = 'a123123abc';
		$in2 = '123abc444';
		$expected = 'a123123abc444';
		$res = $bto->replaceOverlap($in1,$in2);
		$this->assertEquals($expected,$res);
    }
    
    function testreplaceOverlapWithNoOverlap() {
    	$bto = new BaseTemplateObject(1,new SearchResult());
		$in1 = 'abc';
		$in2 = '123';
		$expected = 'abc123';
		$res = $bto->replaceOverlap($in1,$in2);
		$this->assertEquals($expected,$res);
    }
    
    function testreplaceOverlapWithBugExperienced() {
        $bto = new BaseTemplateObject(1,new SearchResult());
        $in1 = 'http://alextest.ratow.dk/htdocs/catalog';
        $in2 = '/htdocs/catalog/advanced_search_result.php';
        $expected = 'http://alextest.ratow.dk/htdocs/catalog/advanced_search_result.php';
        $res = $bto->replaceOverlap($in1,$in2);
        $this->assertEquals($expected,$res);
    }
    
    function testreplaceOverlapWithBugExperienced2() {
        $bto = new BaseTemplateObject(1,new SearchResult());
        $in1 = 'http://alextest.ratow.dk/htdocs/catalog/';
        $in2 = '/htdocs/catalog/advanced_search_result.php';
        $expected = 'http://alextest.ratow.dk/htdocs/catalog/advanced_search_result.php';
        $res = $bto->replaceOverlap($in1,$in2);
        $this->assertEquals($expected,$res);
    }
    
}


