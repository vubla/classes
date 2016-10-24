<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';

$suite  = new PHPUnit_Framework_TestSuite("UpdatequeryTest");




class UpdatequeryTest extends BaseDbTest 
{
    var $wid = 1;
    function setUp() {
        $this->buildDatabases();
        $this->update = new UpdateQuery('words',$this->shopVdo);
    }

    function tearDown() {
        unset($this->update);
        $this->dropDatabases();
    }
    
    function testConvertToSql() 
    {
        $this->update->set('rank','1')->set('word_tmp','derp');
        $result = $this->update->convertToSqlString($this->shopVdo);
        $this->assertContains('UPDATE words',$result);
        $this->assertContains('SET',$result);
        $this->assertContains('rank=\'1\'',$result);
        $this->assertContains('word_tmp=\'derp\'',$result);
        $this->assertNotContains('WHERE',$result);
    }
    
    function testConvertToSqlNoVdo() 
    {
        $update = new UpdateQuery('words');
        $update->set('rank','1')->set('word_tmp','derp');
        try
        {
            $result = $update->convertToSqlString();
            $this->assertFalse(true,'MissingArgumentException should have been trown');
        }
        catch(MissingArgumentException $e)
        {
        }
    }
    
    function testConvertToSqlNoSet() 
    {
        try
        {
            $result = $this->update->convertToSqlString($this->shopVdo);
            $this->assertFalse(true,'MissingArgumentException should have been trown');
        }
        catch(MissingArgumentException $e)
        {
        }
    }
    
    function testConvertToSqlWithWhere() 
    {
        $data1 = 'rank=1';
        $data2 = 'word LIKE %some%';
        $this->update->set('word_tmp','derp')->where($data1)->where($data2);
        $result = $this->update->convertToSqlString($this->shopVdo);
        $this->assertContains('WHERE',$result);
        $this->assertContains($data1,$result);
        $this->assertContains('AND',$result);
        $this->assertContains($data2,$result);
    }
    
    function testExecuteWithWhere() 
    {
        $data = 'word LIKE \'dvd\'';
        $this->update->set('rank','13')->where($data);
        $result = $this->update->execute();
        $this->assertEquals(1,$result);
        
        $result = $this->shopVdo->fetchOne('SELECT rank FROM words WHERE '.$data);
        $this->assertEquals(13,$result);
    }
}