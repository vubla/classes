<?php


require_once '../vublamailer.php';
require_once '../basedbtest.php';
$suite  = new PHPUnit_Framework_TestSuite("DeployDbMaintainerTest");

/**
 * Test edition of \DeoplyDbMaintainer
 * 
 * @package testclasses
 */
class TestDeployDbMaintainer extends DeployDbMaintainer
{
    function setMeta($sql)
    {
        $this->meta_sql = $sql;
    }
    
    function setWebshop($sql)
    {
        $this->webshop_sql = $sql;
        
    }
    
    function getMeta()
    {
        return $this->meta_sql;   
    }
    
    function getWebshop()
    {
        return $this->webshop_sql;  
    }
}

define('DEPLOY_DB_HOOKS_PATH', '../test_deploy_db_hooks');
define('OLD_DEPLOY_DB_HOOKS_PATH', '../test_old_db_hooks');

/**
 * Test class for \DeployDbMaintainer
 * @package testclasses
 * @author Vubla
 */
class DeployDbMaintainerTest extends BaseDbTest 
{
  
    function setUp() {
       $this->buildDatabases();
       $this->c = new TestDeployDbMaintainer();
    }
    
    function tearDown() {
       unset($this->c);
       $this->dropDatabases();
    }
    
    function testDummy()
    
    {
        $this->assertTrue(true);
    }
	/*
    function testLoad(){
        $this->c->load();
        $res1 = $this->c->getWebshop();
        $res2 = $this->c->getMeta();
        $this->assertEquals(file_get_contents(DEPLOY_DB_HOOKS_PATH.'/w_test'),$res1);
        $this->assertEquals(file_get_contents(DEPLOY_DB_HOOKS_PATH.'/m_test'),$res2);
    }
    
    function testVerify1(){
        $this->c->setMeta('insert into table johnson value(1,2);');
        $res = $this->c->verify();
        $this->assertFalse($res);
    }
    
    function testVerify2(){
        $this->c->setMeta(file_get_contents(DEPLOY_DB_HOOKS_PATH.'/m_test'));
        $res = $this->c->verify();
        $this->assertTrue($res);
    }
    
    
    function testDeploy(){
        $this->c->setMeta(file_get_contents(DEPLOY_DB_HOOKS_PATH.'/m_test'));
        $this->c->setWebshop(file_get_contents(DEPLOY_DB_HOOKS_PATH.'/w_test'));
        $this->c->deploy();
        $vdo = vpdo::getVdo(DB_METADATA);
        
        $res = $vdo->fetchOne("select TABLE_NAME from information_schema.TABLES where TABLE_SCHEMA = '".DB_METADATA."' and TABLE_NAME = 'test' limit 1");
        $this->assertEquals('test', $res);
        
        $res = $vdo->fetchOne("select TABLE_NAME from information_schema.TABLES where TABLE_SCHEMA = '".DB_PREFIX."1' and TABLE_NAME = 'test' limit 1");
        $this->assertEquals('test', $res);
        
        $res = $vdo->fetchOne('select id from test limit 1');
        $this->assertEquals(100, $res);
        
        $vdo = vpdo::getVdo(DB_PREFIX.'1');
        $res = $vdo->fetchOne('select id from test limit 1');
        $this->assertEquals(12, $res);
        
        $res = $this->c->isVerified();
        $this->assertTrue($res);
        
    }
   
   
     function testDeployFailure(){
        $this->c->setMeta(file_get_contents(DEPLOY_DB_HOOKS_PATH.'/m_test'). ' insert into johnson values(1,2);');
        $this->c->setWebshop(file_get_contents(DEPLOY_DB_HOOKS_PATH.'/w_test'). ' insert into johnson values(1,2);');
        $this->c->deploy();
        $vdo = vpdo::getVdo(DB_METADATA);
        
        $res = $vdo->fetchOne("select TABLE_NAME from information_schema.TABLES where TABLE_SCHEMA = '".DB_METADATA."' and TABLE_NAME = 'test' limit 1");
        $this->assertEquals(null, $res);
        
        $res = $vdo->fetchOne("select TABLE_NAME from information_schema.TABLES where TABLE_SCHEMA = '".DB_PREFIX."1' and TABLE_NAME = 'test' limit 1");
        $this->assertEquals(null, $res);
        
        
        $res = $this->c->isVerified();
        $this->assertFalse($res);
        
    }
     
    function testCleanup(){
        @exec("rm -r " .DEPLOY_DB_HOOKS_PATH .".backup");
        exec("cp -r ".DEPLOY_DB_HOOKS_PATH." " . DEPLOY_DB_HOOKS_PATH . ".backup");
        exec("rm " .OLD_DEPLOY_DB_HOOKS_PATH ."/*");
         
        
        $this->assertInternalType('array',scandir(DEPLOY_DB_HOOKS_PATH), 'Make sure it was not empty in the first place');
        
        
        $this->c->cleanup();
        $res = scandir(OLD_DEPLOY_DB_HOOKS_PATH);
        $this->assertInternalType('array',$res);
        $this->assertContains('m_'.time(), $res,'This test fails if it takes more than a second (Drop it if it causes trouble)');
        $this->assertFalse($res);
        
        exec("rm " .DEPLOY_DB_HOOKS_PATH ."/*");
        exec("rm " .OLD_DEPLOY_DB_HOOKS_PATH ."/*");
        exec("cp -r ".DEPLOY_DB_HOOKS_PATH.".backup " . DEPLOY_DB_HOOKS_PATH);
       
      
    }
     * 
     */
}






