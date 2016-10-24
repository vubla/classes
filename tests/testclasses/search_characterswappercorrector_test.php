<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';

$suite  = new PHPUnit_Framework_TestSuite("CharacterswappercorrectorTest");




class CharacterswappercorrectorTest extends BaseDbTest 
{
    var $wid = 1;
    function setUp() {
        $this->buildDatabases();
        $this->corrector = new CharacterSwapperCorrector($this->wid);
    }

    function tearDown() {
        unset($this->corrector);
        $this->dropDatabases();
    }
    
    function testSimpleSwap() 
    {
        $data = new SearchWord('ddv');
        $result = $this->corrector->correct(array($data));
        $this->assertCount(1,$result,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        $this->assertCount(1,$result['ddv'],"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        $this->assertEquals('dvd',$result['ddv'][0]->word,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
    }
    
    function testOtherSwap() 
    {
        $data = new SearchWord('belwo');
        $result = $this->corrector->correct(array($data));
        $this->assertCount(1,$result,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        $this->assertCount(1,$result['belwo'],"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        $this->assertEquals('below',$result['belwo'][0]->word,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
    }
}