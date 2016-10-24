<?php
require_once '../vublamailer.php';
require_once '../basedbtest.php';
 


$suite  = new PHPUnit_Framework_TestSuite("RankingWordSelectorTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);



class RankingWordSelectorTest extends BaseDbTest 
{
    function setUp() {
        $this->wid = 1;
        $this->buildDatabases();
        $this->selector = new RankingWordSelector($this->wid);
        $this->gstar = new SearchWord('G-star'); $this->gstar->rank = 100;
        $this->star = new SearchWord('star'); $this->star->rank = 5;
        $this->gatar = new SearchWord('gatar'); $this->gatar->rank = 10;
        
        $this->busker = new SearchWord('busker'); $this->busker->rank = 0;
        $this->bukser = new SearchWord('bukser'); $this->bukser->rank = 100;
        $this->buske = new SearchWord('buske'); $this->buske->rank = 0;
        $this->data = array(
            $this->gstar->word => array($this->star,$this->gatar),
            $this->busker->word => array($this->bukser,$this->buske)
        );
        
        $this->addword($this->gstar);
        $this->addword($this->star);
        $this->addword($this->gatar);
        $this->addword($this->busker);
        $this->addword($this->bukser);
        $this->addword($this->buske);
    }

    function tearDown() {
        unset($this->selector);
        $this->dropDatabases();
    } 
    
    private function addword(SearchWord $word)
    {
        $this->shopVdo->exec("insert into words(word,rank) VALUES ('{$word->word}','{$word->rank}')");
    }
    
    function testSelectWordArraysOne() 
    {
        $result = $this->selector->selectWordArrays($this->data,1);
        
        $this->assertCount(1,$result);//,"result: ".print_r($result,true)." on '".print_r($this->data,true)."'");
        $this->assertCount(2,$result[0]);//,"result: ".print_r($result,true)." on '".print_r($this->data,true)."'");
        $this->assertEquals($this->gstar,$result[0][0]);//,"result: ".print_r($result,true)." on '".print_r($this->data,true)."'");
        $this->assertEquals($this->bukser,$result[0][1]);//,"result: ".print_r($result,true)." on '".print_r($this->data,true)."'");
    }  
    
    function testSelectWordArraysFour() 
    {
        $result = $this->selector->selectWordArrays($this->data,4);
        
        $this->assertCount(4,$result,"result: ".print_r($result,true)." on '".print_r($this->data,true)."'");
        for($i = 0 ; $i < 3 ; $i++) {
            $this->assertCount(2,$result[$i],"result: ".print_r($result,true)." on '".print_r($this->data,true)."'");
        }
        
        $this->assertEquals(array($this->gstar,$this->bukser),$result[0]);
        $this->assertEquals(array($this->gatar,$this->bukser),$result[1]);
        $this->assertEquals(array($this->star,$this->bukser),$result[2]);
        $this->assertEquals(array($this->gstar,$this->busker),$result[3]);
    } 
}


