<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';




$suite  = new PHPUnit_Framework_TestSuite("SearcherTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);

class SearcherTest extends BaseDbTest 
{
    function setUp() 
    {
          $this->wid = 1;
        $this->buildDatabases(); 
        $_GET = array();
    }

    function tearDown() 
    {
        $this->wid = 1;
        $this->dropDatabases();
        $_GET = array();
    }
    function testUseRankedSearch()
    {
            
        $data = 'dvd';
        $h = new Searcher(1,$data);
        VDO::webshop(1)->exec('update words set rank = 1 limit ' . 192);
        
        settings::setLocal('ranked_search_threshold', 0, 1);
        $this->assertTrue($h->useRankedSearch());
        settings::setLocal('ranked_search_threshold', 100, 1);
        $this->assertFalse($h->useRankedSearch());
          settings::setLocal('ranked_search_threshold', -1, 1);
        $this->assertTrue($h->useRankedSearch());
        settings::setLocal('ranked_search_threshold', 101, 1);
        $this->assertFalse($h->useRankedSearch());
        settings::setLocal('ranked_search_threshold', 23, 1);
        $this->assertTrue($h->useRankedSearch());
        
        settings::setLocal('ranked_search_threshold', 24, 1);
        $this->assertTrue($h->useRankedSearch());
        
        settings::setLocal('ranked_search_threshold', 25, 1);
        $this->assertTrue($h->useRankedSearch());
         settings::setLocal('ranked_search_threshold', 26, 1);
        $this->assertFalse($h->useRankedSearch());
             settings::setLocal('ranked_search_threshold', 75, 1);
        $this->assertFalse($h->useRankedSearch());
    }
    
    function testSomethingShouldBeFound()
    {
        $_GET['host'] = 'everlight.dk';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 1;
        
        $_GET['postvar'] = json_encode(array('name'=>1,3,4,5));
        $_GET['getvar'] = json_encode(array('name'=>1,3,4,5));
        Settings::setLocal('enabled',1,1);
        $data = 'dvd';
        $h = new Searcher(1,$data);
      //    var_dump($_GET);
       // $h->initialize();
        
        $res = $h->getResults(array());
      
     
        $this->assertInstanceOf('SearchResult', $res);
        $this->assertCount(18,$res->products);
    }
    
    function testNothingShouldBeFound()
    {
        $_GET['host'] = 'everlight.dk';
        $_GET['q'] = 'dvd';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 1;
        $_GET['postvar'] = json_encode(array('name'=>1,3,4,5));
        $_GET['getvar'] = json_encode(array('name'=>1,3,4,5));
        Settings::setLocal('enabled',1,1);
        $data = 'ssadfadssfafsafsafsafgdsafgsgdsadfghd';
        $h = new Searcher(1,$data);
      //    var_dump($_GET);
       // $h->initialize();
        
        $res = $h->getResults(array());
      
     
        $this->assertInstanceOf('SearchResult', $res);
        $this->assertEmpty($res->products);
    }
    
     function testNothingShouldBeFoundWithSortByAndSortOrder()
    {
        $_GET['host'] = 'everlight.dk';
      
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 1;
        $_GET['sort_by'] = 'name@asc';
        $_GET['postvar'] = json_encode(array('name'=>1,3,4,5));
        $_GET['getvar'] = json_encode(array('name'=>1,3,4,5));
        Settings::setLocal('enabled',1,1);
        $data = 'ssadfadssfafsafsafsafgdsafgsgdsadfghd';
        $h = new Searcher(1,$data);
      //    var_dump($_GET);
       // $h->initialize();
        
        $res = $h->getResults(array());
      
     
        $this->assertInstanceOf('SearchResult', $res);
        $this->assertEmpty($res->products);
    }
    
    function testSomethingWithSortBy()
    {
        $_GET = null;
        $_GET['host'] = 'everlight.dk';
       
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 1;
        
        $_GET['sort_by'] = 'lowest_price';
        $_GET['postvar'] = json_encode(array('name'=>1,3,4,5));
        $_GET['getvar'] = json_encode(array('name'=>1,3,4,5));
        Settings::setLocal('enabled',1,1);
        $data = 'dvd';
        $h = new Searcher(1,$data);
      //    var_dump($_GET);
       // $h->initialize();
        
        $res = $h->getResults(array());
      
     
        $this->assertInstanceOf('SearchResult', $res);
        $this->assertCount(18,$res->products);
    }
    
    function testFindItemsWithOptions()
    {
        Settings::setLocal('enabled',1,1);
        $data = 'dvd';
        
        $h = new Searcher(1,$data);
        $h->setEqOptions(array('category_id'=>array(1)));
        
        $res = $h->getResults(array());
        
        $this->assertInstanceOf('SearchResult', $res);
        $this->assertCount(1,$res->products);
        $this->assertEquals('Matrox G400 32MB',$res->products[0]->name);
    }
    
    function testFindNoItemWithOptions()
    {
        Settings::setLocal('enabled',1,1);
        $data = '';
        $h = new Searcher(1,$data);
        $h->setEqOptions(array('category_id'=>array(242135234543)));
        
        $res = $h->getResults(array());
        //var_dump($res);exit;
        
        $this->assertInstanceOf('SearchResult', $res);
        $this->assertCount(0,$res->products, 'The products found are: '.print_r($res->products,true));
    }

    function testCheckThatDidYouMeanIsSet()
    {
        Settings::setLocal('enabled',1,1);
        $data = 'matris';
        $h = new Searcher(1,$data);
        
        $res = $h->getResults(array());

        $this->assertInstanceOf('SearchResult', $res);
        $this->assertCount(1,$res->did_you_mean, 'Did you mean was: '.print_r($res->did_you_mean,true));
        $this->assertInternalType('array',$res->did_you_mean[0] );
        $this->assertInstanceOf('SearchWord',$res->did_you_mean[0][0], 'Did you mean was: '.print_r($res->did_you_mean,true));
        $this->assertEquals('matrix',$res->did_you_mean[0][0]->short, 'Did you mean was: '.print_r($res->did_you_mean,true));
    }


}

