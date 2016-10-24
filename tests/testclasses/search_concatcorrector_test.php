<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';

$suite  = new PHPUnit_Framework_TestSuite("ConcatcorrectorTest");

class ConcatcorrectorTest extends BaseDbTest 
{
    var $wid = 1;
    var $corrector;
    
    function setUp() {
        $this->buildDatabases(); 
        $this->corrector = new ConcatCorrector($this->wid);
    }

    function tearDown() {
        unset($this->corrector);
        $this->dropDatabases();
    }
    
    function testCorrectFindOne() 
    {
        $data = array('multi','monito');
        $result = $this->corrector->correct($data);
        $this->assertCount(2,$result,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        $this->assertCount(1,$result[$data[0]],"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        $this->assertEquals($data[0].$data[1],$result[$data[0]][0]->word,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        $this->assertCount(1,$result[$data[1]],"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        $this->assertEquals($data[0].$data[1],$result[$data[1]][0]->word,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
    }
    
    function testCorrectFindNone() 
    {
        $data = array('abc','def');
        $result = $this->corrector->correct($data);
        $this->assertCount(2,$result,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        $this->assertCount(0,$result[$data[0]],"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        $this->assertCount(0,$result[$data[1]],"result: ".print_r($result,true)." on '".print_r($data,true)."'");
    }

    
    function testCorrectManyWords() 
    {
        $data = array('multi','monito','award','winning');
        $result = $this->corrector->correct($data);
        $this->assertCount(4,$result,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        $this->assertCount(1,$result[$data[0]],"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        $this->assertEquals($data[0].$data[1],$result[$data[0]][0]->word,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        $this->assertCount(1,$result[$data[1]],"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        $this->assertEquals($data[0].$data[1],$result[$data[1]][0]->word,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        
        $this->assertCount(1,$result[$data[2]],"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        $this->assertEquals($data[2].$data[3],$result[$data[2]][0]->word,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        $this->assertCount(1,$result[$data[3]],"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        $this->assertEquals($data[2].$data[3],$result[$data[3]][0]->word,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
    }
}