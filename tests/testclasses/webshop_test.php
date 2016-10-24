<?php
require_once '../vublamailer.php';
require_once '../basedbtest.php';
$suite  = new PHPUnit_Framework_TestSuite("WebshopDbManagerTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);



class WebshopTest extends BaseDbTest 
{
    function setUp() 
    {
       $this->wid = 1;
       $this->buildDatabases(); 
       $this->webshop = new Webshop(1);
     
      

    }

    function tearDown() 
    {
       
        $this->dropDatabases();
   
    }
   
    function testGetChildren()
    {
    	$result = (new Webshop(3))->getChildren();
        sort($result);
        $this->assertEquals(array(new Webshop(1),new Webshop(2)), ($result));

        $result = (new Webshop(1))->getChildren();
        sort($result);
        $this->assertEquals(array(), ($result));
    }

    function testGetSibling()
    {
        $result = $this->webshop->getSiblings();
        sort($result);
        $this->assertEquals(array(new Webshop(1),new Webshop(2)), ($result));
        
        $result = (new webshop(3))->getSiblings();
        sort($result);
        $this->assertEquals(array(new Webshop(3)), ($result));
        
    } 

    function testGetParent()
    {
        $result = $this->webshop->getParent();
        $this->assertEquals(new Webshop(3), $result);
        
    } 

    function testGetFamily()
    {
        $result = $this->webshop->getFamily();
        sort($result);
        $this->assertEquals(array(new Webshop(1),new Webshop(2),new Webshop(3)), ($result));
        
        $result = (new webshop(3))->getFamily();
        sort($result);
        $this->assertEquals(array(new Webshop(1),new Webshop(2),new Webshop(3)), ($result));
        
    } 

    function testAllForNonExistingValues()
    {
    	$webshop = new Webshop(6);
    	$result = $webshop->getFamily();
    	$this->assertEquals(array(new Webshop(6)), $result);

    	$result = $webshop->getParent();
    	$this->assertEquals(null, $result);

    	$result = $webshop->getSiblings();
    	$this->assertEquals(array(new Webshop(6)), $result);

    	$result = $webshop->getChildren();
    	$this->assertEquals(array(), $result);
    }

    

}
