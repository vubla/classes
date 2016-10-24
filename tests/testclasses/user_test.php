<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';
$suite  = new PHPUnit_Framework_TestSuite("UserTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);


class TestUser extends User{
	
	private static $salt = 'Super Secret Salti7lkumtnryehj678li,5muy57k657i,tyjrmhentyj374k6i,jmrythenry357k64ury,j hmnrwyjku,yrkhm yjkry,jmhrjykue,tjmhjykjkeutjmhjruektu';
    
	 function __construct() {
		$this->db = VPDO::getVdo(DB_METADATA);
       
	}
	
	function changePassword($cid,$data){ return parent::changePassword($cid,$data); }
	function setPassword($cid,$newPassword, $eee = 'dd') { return parent::setPassword($cid,$newPassword); }
}

class UserTest extends BaseDbTest 
{
	
	 private static $salt = 'Super Secret Salti7lkumtnryehj678li,5muy57k657i,tyjrmhentyj374k6i,jmrythenry357k64ury,j hmnrwyjku,yrkhm yjkry,jmhrjykue,tjmhjykjkeutjmhjruektu';
   
    
    function setUp() 
    {
      
        $this->buildDatabases(); 
        $this->pdo = VPDO::getVdo(DB_METADATA);
     	$this->oldpassword = '7a4530e86bbd983d79bea32ee8706d64';
      

    }

    function tearDown() 
    {
       
        $this->dropDatabases();
   
    }
    
	function testSetPassword(){
		
		
		$data = array();
		$data['oldPassword'] = 'Trekant01';
		$data['password'] = 'JohnnyMadsen';
		$data['password2'] = $data['password'];
		$res = $this->pdo->fetchOne('select pwd from customers where id = 1');
		$this->assertEquals(md5('Trekant01'.self::$salt), $res);
		$subject = new TestUser();
		
		$respond = $subject->changePassword(1,$data);
		
		$res = $this->pdo->fetchOne('select pwd from customers where id = 1');
		$this->assertEquals(md5($data['password'].self::$salt), $res, print_r($respond,true));
		
	}
   
}

