<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';
$suite  = new PHPUnit_Framework_TestSuite("CategoryTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);

class TestScraper extends Scraper {
    function getFetcher(){
        
    }
    
    
}

class CategoryTest extends BaseDbTest 
{
    private $test_max_prod;
    private $initial;
    
    function setUp() {
        $this->buildDatabases();
       
    }
    
    function tearDown() {
      ScrapeMode::set('full');
      $this->dropDatabases();
    }
    
    function testSave(){
        $s = new TestScraper(1);
        $s->prepare();
        $cat = new Category(1);
        $res1 = $cat->save();
        $this->assertFalse($res1);
       
        $cat = new Category(1);
        $cat->parent_id = 1;
        $cat->cid = 200;
        $cat->name = 'SomeCat';
        $cat->description = 'Something';
        $res2 = $cat->save();
        $this->assertTrue($res2);
        
        $vdo = VPDO::getVdo(DB_PREFIX.'1');
        $stm = $vdo->prepare('select * from categories'.ScrapeMode::getPF().' where cid = ? limit 1');
        $stm->execute(array($cat->cid));
        $result = $stm->fetchAll();
        $stm->closeCursor();
        $this->assertInternalType('array',$result);
        $this->assertCount(1,$result);
        $this->assertEquals($cat->cid,$result[0]['cid']);
        $this->assertEquals('SomeCat',$result[0]['name']);
        $this->assertEquals('1',$result[0]['parent_id']);
        $this->assertEquals('Something',$result[0]['description']);
    }
   
    function testSaveWithUpdate(){
        // If we try to save the same category twice, it should not be a problem 
        // And it does not matter whether we are in update or full mode.
        ScrapeMode::set('update');
        $this->testSave();
        $this->testSave();
    }
 

    
    
}


