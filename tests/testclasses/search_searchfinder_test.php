<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';




$suite  = new PHPUnit_Framework_TestSuite("SearcFinderTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);

class SearcFinderTest extends BaseDbTest 
{
    function setUp() 
    {
        $this->buildDatabases(); 
        $this->c = SearchFinder::create(1);    
    }

    function tearDown() 
    {
        $this->dropDatabases();
        
    }
    
    function testThatItHalts()
    {
        
        $words = array(new SearchWord('test1'),array(array(array(new SearchWord('test12'),new SearchWord('test13',array(new SearchWord('test14'),new SearchWord('test15')))))));
        $this->c->addSearchWords($words);
        $res = $this->c->getRelatedSearches();
        $this->assertCount(0, $res);
    }
        
    function testWeFindThreeSpeedsButNot4()
    {
        vdo::webshop(1)->exec("update words set rank = 1000 where word = 'speed'");
        vdo::webshop(1)->exec("update words set rank = 2000 where word = 'speed2'");
        vdo::webshop(1)->exec("update words set rank = 1 where word = 'dvdspeed'");
        vdo::webshop(1)->exec("update words set rank = 2 where word = 'dvdspeed2'");
        $input = array('speed','speed2', 'dvdspeed');
        $input2 = array('dvdspeed2');
        $this->c->addSearchWords($input)
                ->addSearchWords($input2);
        
        
       
      
        
        
        $expected = array('speed','speed2', 'dvdspeed2');
        $res = $this->c->getRelatedSearches();
        $result = array(); 
        foreach($res as $SearchWord)
        {
           
            $result[] = $SearchWord->__toString();
        }
        sort($result);
        sort($expected);
        $this->assertEquals($expected, $result);
    }

}

