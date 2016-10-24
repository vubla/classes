<?php


require_once '../vublamailer.php';
require_once '../basedbtest.php';

$suite  = new PHPUnit_Framework_TestSuite("DictoryTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);





class DictoryTest extends BaseDbTest 
{
   
   
    
    function setUp() {
        $this->buildDatabases();
     
    }
    
    function tearDown() {
        $this->dropDatabases();
    }
    
    function testGetThesaurus(){
        $data = new Word('dvd');
        $res = Dictionary::getThesaurus($data);
        
        $this->assertInternalType('array', $res,"result: ".print_r($res,true));
        $this->assertArrayHasProperty('skive', $res, 'word');
    }
    
    function testGetThesaurus2(){
        $data = new Word('skive');
        $res = Dictionary::getThesaurus($data);
        $this->assertInternalType('array', $res, "result: ".print_r($res,true));
        $this->assertArrayHasProperty('dvd', $res, 'word');
    }
    
    function testGetThesaurus3(){
        $data = new Word('lp');
        $res = Dictionary::getThesaurus($data);
        $this->assertInternalType('array', $res, "result: ".print_r($res,true));
        $this->assertArrayHasProperty('skive', $res, 'word');
    }
    
    

}






