<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';

$suite  = new PHPUnit_Framework_TestSuite("LevenshteinspellingcorrectorTest");




class LevenshteinspellingcorrectorTest extends BaseDbTest 
{
    var $wid = 1;
    function setUp() {
        $this->buildDatabases();
        $this->corrector = new LevenshteinSpellingCorrector($this->wid);
    }

    function tearDown() {
        unset($this->corrector);
        $this->dropDatabases();
    }
    
    function testExpandTowardsSpellingMistakesSucces() 
    {
        $data = new SearchWord('matris');
        $result = $this->corrector->correct(array($data));
        $this->assertCount(1,$result,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        $this->assertEquals('matrix',$result['matris'][0]->word,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
    }
}