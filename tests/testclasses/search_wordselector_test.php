<?php
require_once '../vublamailer.php';
require_once '../basedbtest.php';
 


$suite  = new PHPUnit_Framework_TestSuite("WordSelectorTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);

class WordSelectorObject extends WordSelector  
{
    function selectWords(array $words, $number = 0)
    {
        return array_slice($words, 0,$number);
    }
}



class WordSelectorTest extends BaseDbTest 
{
    function setUp() {
        $this->wid = 1;
        $this->buildDatabases();
        $this->selector = new WordSelectorObject($this->wid);
        $this->gstar = new SearchWord('G-star');
        $this->star = new SearchWord('star');
        $this->gatar = new SearchWord('gatar');
        $this->busker = new SearchWord('busker');
        $this->bukser = new SearchWord('bukser');
        $this->buske = new SearchWord('buske');
        $this->data = array(
            $this->gstar->word => array($this->star,$this->gatar),
            $this->busker->word => array($this->bukser,$this->buske)
        );
    }

    function tearDown() {
        unset($this->selector);
        $this->dropDatabases();
        $this->wid = 1;
    } 
    
    function testSelectWordArraysOne() 
    {
        $result = $this->selector->selectWordArrays($this->data,1);
        
        $this->assertCount(1,$result,"result: ".print_r($result,true)." on '".print_r($this->data,true)."'");
        $this->assertCount(2,$result[0],"result: ".print_r($result,true)." on '".print_r($this->data,true)."'");
        $this->assertEquals($this->gstar,$result[0][0],"result: ".print_r($result,true)." on '".print_r($this->data,true)."'");
        $this->assertEquals($this->busker,$result[0][1],"result: ".print_r($result,true)." on '".print_r($this->data,true)."'");
    }  
    
    function testSelectWordArraysFour() 
    {
        $result = $this->selector->selectWordArrays($this->data,4);
        
        $this->assertCount(4,$result,"result: ".print_r($result,true)." on '".print_r($this->data,true)."'");
        for($i = 0 ; $i < 4 ; $i++) {
            $this->assertCount(2,$result[$i],"result: ".print_r($result,true)." on '".print_r($this->data,true)."'");
        }
        
        $this->assertEquals(array($this->gstar,$this->busker),$result[0],"result: ".print_r($result,true)." on '".print_r($this->data,true)."'");
        $this->assertEquals(array($this->gstar,$this->bukser),$result[1],"result: ".print_r($result,true)." on '".print_r($this->data,true)."'");
        $this->assertEquals(array($this->gstar,$this->buske),$result[2],"result: ".print_r($result,true)." on '".print_r($this->data,true)."'");
        $this->assertEquals(array($this->star,$this->busker),$result[3],"result: ".print_r($result,true)." on '".print_r($this->data,true)."'");
        //$this->assertEquals(array($this->star,$this->buske),$result[3],"result: ".print_r($result,true)." on '".print_r($this->data,true)."'");
    } 
}


