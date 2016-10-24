<?php

require '../vublamailer.php';
require '../basedbtest.php';

$suite  = new PHPUnit_Framework_TestSuite("SearchHandlerTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);


class SearchHandlerTestObject  extends SearchHandler
{
    function __construct(){
        $this->searcher = new SearcherTestObject(1);
        $this->meta = VPDO::getVdo(DB_METADATA);
      
        $this->wpdo = VPDO::getVdo(DB_PREFIX . 1);
    }
}

class SearcherTestObject extends SearchHandler
{
    var $min_opt;
    var $max_opt;
    var $eq_opt;
    
    function setMinOptions($e){
        $this->min_opt = $e;
    }
    function setMaxOptions($e){
        $this->max_opt = $e;
    }
    function setEqOptions($e){
        $this->eq_opt = $e;
    }
    function setSortBy($e){
        $this->sort_by = $e;
    }
    function setSortOrder($e){
        $this->sortorder = $e;
    }
  
}


class SearchHandlerTest extends BaseDbTest 
{
    function setUp() 
    {
      
        $this->buildDatabases(); 
       
        $_GET = array();
      

    }

    function tearDown() 
    {
        unset($this->filter);
        $this->dropDatabases();
        $_GET = array();
    }
    
  
    
    function testNothingShouldBeFound()
    {
        $_GET['host'] = 'everlight.dk';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 1;
        $_GET['postvar'] = json_encode(array('name'=>1,3,4,5));
        $_GET['getvar'] = json_encode(array('name'=>1,3,4,5));
        Settings::setLocal('enabled',1,1);
        $data = 'i';
        $h = new Searcher(1,$data);
        $res = $h->getResults(array());
      
        
     
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
        
        $_GET['sort_by'] = 'price';
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
    
    
 
    
    
   
  
  
}

