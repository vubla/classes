<?php

require_once '../vublamailer.php'; // Not the real one

require_once '../basedbtest.php';



$suite  = new PHPUnit_Framework_TestSuite("SearchHandlerTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);

class MockedSplitTestHandler extends SplitTestHandler
{
	public function __construct($wid) {
		parent::__construct($wid);
	}
	
	public function callgetSearchEngine($ip) {
		return $this->getSearchEngine($ip);
	}
}


class SplitTestHandlerTest extends BaseDbTest 
{
    var $wid = 1;
    function setUp() 
    {
        $this->buildDatabases(); 
        $_GET = array();
    	$this->splitHandler = new MockedSplitTestHandler($this->wid);
    }

    function tearDown() 
    {
        unset($this->splitHandler);
        $this->dropDatabases();
        $_GET = array();
    }
	
	//GetSearchEngine
	
  	function testGetSearchEngine_SettingSet_FirstEntry() 
  	{
		$ip = '1.2.3.4';
		$expected = 'Vubla';
		
		$actual = $this->splitHandler->callgetSearchEngine($ip);
		$this->assertEquals($expected,$actual);
  	}
  
  	function testGetSearchEngine_SettingSet_FirstEntryAgain() 
  	{
		$ip = '1.2.3.4';
		$expected = 'Vubla';
		
		$this->splitHandler->callgetSearchEngine($ip);
		$actual = $this->splitHandler->callgetSearchEngine($ip);
		$this->assertEquals($expected,$actual);
  	}
  
  	function testGetSearchEngine_SettingSet_SecondEntry() 
  	{
		$ip = '1.2.3.4';
		$expected = 'Native';
		
		$this->splitHandler->callgetSearchEngine($ip);
		$ip = '4.3.2.1';
		
		$actual = $this->splitHandler->callgetSearchEngine($ip);
		$this->assertEquals($expected,$actual);
  	}
  
  	function testGetSearchEngine_SettingSet_SecondEntryAgain() 
  	{
		$ip = '1.2.3.4';
		$expected = 'Native';
		
		$this->splitHandler->callgetSearchEngine($ip);
		
		$ip = '4.3.2.1';
		$this->splitHandler->callgetSearchEngine($ip);
		$actual = $this->splitHandler->callgetSearchEngine($ip);
		$this->assertEquals($expected,$actual);
  	}
	
	//UseVubla
  
  	function testUseVubla_SettingNotSet() 
  	{
		$actual = $this->splitHandler->useVubla();
		$this->assertTrue($actual);
  	}
  
    /**
	 * @expectedException VublaException
	 */
  	function testUseVubla_SettingSet_NoIp() 
  	{
  		Settings::setLocal('split_test',1,$this->wid);
		if(Settings::get('split_test',$this->wid) != 1) {
			$this->markTestIncomplete('split_test settings is not set');
		}
		$_GET['ip'] = NULL;
		$actual = null;
		
		$actual = $this->splitHandler->useVubla();
		$this->assertNull($actual);
  	}
  
  	function testUseVubla_SettingSet_FirstEntry() 
  	{
  		Settings::setLocal('split_test',1,$this->wid);
		if(Settings::get('split_test',$this->wid) != 1) {
			$this->markTestIncomplete('split_test settings is not set');
		}
		
		$_GET['ip'] = '1.2.3.4';
		
		$actual = $this->splitHandler->useVubla();
		$this->assertTrue($actual);
  	}
  
  	function testUseVubla_SettingSet_FirstEntryAgain() 
  	{
  		Settings::setLocal('split_test',1,$this->wid);
		if(Settings::get('split_test',$this->wid) != 1) {
			$this->markTestIncomplete('split_test settings is not set');
		}
		
		$_GET['ip'] = '1.2.3.4';
		
		$this->splitHandler->useVubla();
		$actual = $this->splitHandler->useVubla();
		$this->assertTrue($actual);
  	}
  
  	function testUseVubla_SettingSet_SecondEntry() 
  	{
  		Settings::setLocal('split_test',1,$this->wid);
		if(Settings::get('split_test',$this->wid) != 1) {
			$this->markTestIncomplete('split_test settings is not set');
		}
		
		$_GET['ip'] = '1.2.3.4';
		
		$this->splitHandler->useVubla();
		$_GET['ip'] = '4.3.2.1';
		
		$actual = $this->splitHandler->useVubla();
		$this->assertFalse($actual);
  	}
  
  	function testUseVubla_SettingSet_SecondEntryAgain() 
  	{
  		Settings::setLocal('split_test',1,$this->wid);
		if(Settings::get('split_test',$this->wid) != 1) {
			$this->markTestIncomplete('split_test settings is not set');
		}
		
		$_GET['ip'] = '1.2.3.4';
		
		$this->splitHandler->useVubla();
		
		$_GET['ip'] = '4.3.2.1';
		$this->splitHandler->useVubla();
		$actual = $this->splitHandler->useVubla();
		$this->assertFalse($actual);
  	}
}

