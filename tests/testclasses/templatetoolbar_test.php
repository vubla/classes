<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';



$suite  = new PHPUnit_Framework_TestSuite("TemplateToolbarTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);





class TemplateToolbarTest extends BaseDbTest 
{
    private $test_max_prod;
    private $initial;
    function setUp() {
       $this->buildDatabases();
    
    }

    
   
    function tearDown() {
        
            
          $this->dropDatabases();
    }
    
    
    function testGenerateMsg(){
        $data = new SearchResult();
        $data->did_you_mean = array(array('xenon', 'h7'), array('xeno', 'h7'));
        $data->original = 'zenon h7';
        $obj = new TemplateToolbar(1,$data);
        $res = $obj->generateMsg();
        
        $this->assertTrue(strpos($res, 'xenon h7') !== false, 'msg were ' . $res);
        $this->assertTrue(strpos($res, 'zenon h7') !== false, 'msg were ' . $res);
        $this->assertTrue(strpos($res, 'xeno h7') !== false, 'msg were ' . $res);
        $this->assertTrue(strpos($res, 'Mente du') !== false, 'msg were ' . $res);
        $this->assertTrue(strpos($res, '0') !== false, 'msg were ' . $res);
    }
    
     function testGenerateMsg2(){
        $data = new SearchResult();
        $data->original = 'zenon h7';
        $obj = new TemplateToolbar(1,$data);
        $res = $obj->generateMsg();
        $this->assertTrue(strpos($res, 'zenon h7') !== false, 'msg were ' . $res);
        $this->assertTrue(strpos($res, '0') !== false, 'msg were ' . $res);
        $this->assertTrue(strpos($res, 'Mente du') === false, 'msg were ' . $res);
    }
    
    
    

    
    
}


