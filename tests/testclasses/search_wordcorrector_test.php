<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';

$suite  = new PHPUnit_Framework_TestSuite("WordcorrectorTest");

class WordCorrectorObject extends WordCorrector {
    protected function _correctWord($word) 
    {
        return array($word);
    }
}



class WordcorrectorTest extends BaseDbTest 
{
    
    var $corrector;
    
    function setUp() {
        $this->wid = 1;
        $this->buildDatabases(); 
        $this->corrector = new WordCorrectorObject($this->wid);
    }

    function tearDown() {
        unset($this->corrector);
        $this->dropDatabases();
        
    }
    
    function testCorrect() 
    {
        $data = array('abc','def');
        $result = $this->corrector->correct($data);
        $this->assertCount(2,$result,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        $this->assertEquals($data[0],$result['abc'][0]->word,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        $this->assertEquals($data[1],$result['def'][0]->word,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
    }

}