<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';
$suite  = new PHPUnit_Framework_TestSuite("WebshopDbManagerTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);




class WebshopDbManagerTest extends BaseDbTest 
{
    function setUp() 
    {
       $this->wid = 1;
        $this->buildDatabases(); 
       
     
      

    }

    function tearDown() 
    {
       
        $this->dropDatabases();
   
    }
   
   

	function testgetPackageFromProducts(){
	    $_SESSION['iso'] = 'da';
        settings::setGlobal('admin_language',1);
        settings::setLocal('admin_language',1,1);
		Language::init(1);
		$data = 100;
        $result = WebshopDbManager::getPackageFromProducts($data);
        $this->assertInstanceOf('stdClass',$result);
        $this->assertEquals(1, $result->id);
		$this->assertEquals(99, $result->price);
		
		$data = 1;
        $result = WebshopDbManager::getPackageFromProducts($data);
        $this->assertInstanceOf('stdClass',$result);
        $this->assertEquals(1, $result->id);
		$this->assertEquals(99, $result->price);
		
		$data = 3;
        $result = WebshopDbManager::getPackageFromProducts($data);
        $this->assertInstanceOf('stdClass',$result);
        $this->assertEquals(1, $result->id);
		$this->assertEquals(99, $result->price);
		
		$data = 500;
        $result = WebshopDbManager::getPackageFromProducts($data);
        $this->assertInstanceOf('stdClass',$result);
        $this->assertEquals(1, $result->id);
		$this->assertEquals(99, $result->price);
		
		$data = 501;
        $result = WebshopDbManager::getPackageFromProducts($data);
        $this->assertInstanceOf('stdClass',$result);
        $this->assertEquals(2, $result->id);
		$this->assertEquals(299, $result->price);
		
		$data = 701;
        $result = WebshopDbManager::getPackageFromProducts($data);
        $this->assertInstanceOf('stdClass',$result);
        $this->assertEquals(2, $result->id);
		$this->assertEquals(299, $result->price);
		
		$data = 5000;
        $result = WebshopDbManager::getPackageFromProducts($data);
        $this->assertInstanceOf('stdClass',$result);
        $this->assertEquals(2, $result->id);
		$this->assertEquals(299, $result->price);
		
		$data = 5001;
        $result = WebshopDbManager::getPackageFromProducts($data);
        $this->assertInstanceOf('stdClass',$result);
        $this->assertEquals(3, $result->id);
		$this->assertEquals(999, $result->price);
		
		$data = 50000;
        $result = WebshopDbManager::getPackageFromProducts($data);
        $this->assertInstanceOf('stdClass',$result);
        $this->assertEquals(3, $result->id);
		$this->assertEquals(999, $result->price);
		
		// What if more than 50000?
		/// For now we go by package 3
		/*data = 50001;
        $result = WebshopDbManager::getPackageFromProducts($data);
        $this->assertInstanceOf('stdClass',$result);
        $this->assertEquals(3, $result->id);
		$this->assertEquals(999, $result->price);
		 * *
		 */
	}
	
   
}

