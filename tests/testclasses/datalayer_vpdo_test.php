<?php


require_once '../vublamailer.php';
require_once '../basedbtest.php';
$suite  = new PHPUnit_Framework_TestSuite("VpdoTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);





class VpdoTest extends BaseDbTest 
{
    function setUp() {
      
        $this->buildDatabases(); 
        
      

    }

    function tearDown() {
      
        $this->dropDatabases();
        
    }
    
    function testFetchSingleArray(){
        $vdo = vpdo::getVdo(DB_PREFIX.'1');
        $result = $vdo->fetchSingleArray('select id from products  where id = ? or id = ?', array(2,3));
        
        $this->assertCount(2, $result, print_r($result,true));
        $this->assertContains(2, $result);
        $this->assertContains(3, $result);
    }
    
   
}

