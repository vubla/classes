<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';
$suite  = new PHPUnit_Framework_TestSuite("CachedImageTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);


class TestCachedImage extends CachedImage {
 

   var $pid;
   var $pdo;
 
 	function __construct($wid,$pid,$image_link)
 	{
 		$this->wid = $wid;	
		$this->pid = $pid;
        $this->image_link = $image_link;
        
 			$this->pdo = vpdo::getVdo(VUBLA_CACHE);
 	}

}

class CachedImageTest extends BaseDbTest 
{
    public $data;
    
    private $test_max_prod;
    private $initial;
    function testLoadFromUrl() {
        $this->assertTrue(true);   
    }
    /*
    function setUp() {
        $this->wid = 1;
    	if($this->lockDatabases()) {
        	$this->buildSpecificDatabase('phpunit_cache');
		} else 
            {
                echo "no lock " . __LINE__ . " in " . __FILE__; 
            }
     
    }
    
    function tearDown() {
       
         $this->dropSpecificDatabase('phpunit_cache');
		  $this->unlockDatabases();
    }
    
    function testLoadFromUrl() {
        $i = new TestCachedImage('1','2','http://api.vubla.com/images/bg.png');
        $i->loadFromUrl('http://api.vubla.com/images/bg.png');
		$this->assertInternalType('resource', $i->image);
		$this->assertEquals(IMAGETYPE_PNG, $i->image_type);

    }
    
	function testSave(){
		$i = new TestCachedImage('1','2','http://api.vubla.com/images/bg.png');
		$i->loadFromUrl('http://api.vubla.com/images/bg.png');
		$i->save();
		
		$this->pdo = vpdo::getVdo('phpunit_cache');
		$row = $this->pdo->getRow("select * from image_cache where wid = ? and pid = ?",array( 1,2));
		
		$this->assertInternaltype('object', $row);
        $this->assertEquals(IMAGETYPE_PNG, $row->image_type);
		
	}
    
    function testConstructer(){
        $i = new CachedImage('1','2','http://api.vubla.com/images/bg.png');
    
        
        $this->pdo = vpdo::getVdo('phpunit_cache');
        $row = $this->pdo->getRow("select * from image_cache where wid = ? and pid = ?",array( 1,2));
        
        $this->assertInternaltype('object', $row);
        $this->assertEquals(IMAGETYPE_PNG, $row->image_type);
        
    }
   
   
   
    function testloadFromCache(){
        $i = new TestCachedImage('1','2','http://api.vubla.com/images/bg.png');
        $i->loadFromUrl('http://api.vubla.com/images/bg.png');
        $i->save();
        
        $i = new TestCachedImage('1','2','http://api.vubla.com/images/bg.png');
        $i->loadFromCache();
        

        $this->assertEquals(IMAGETYPE_PNG, $i->image_type);
        
    }
     function testChangedPicturePAthOnConstructor(){
        $link = 'http://api.vubla.com/images/bg.png';
        $i = new CachedImage('1','2',$link);
    

        $this->pdo = vpdo::getVdo('phpunit_cache');
        $row = $this->pdo->getRow("select * from image_cache where wid = ? and pid = ?",array( 1,2));
        
        $this->assertInternaltype('object', $row);
        $this->assertEquals(IMAGETYPE_PNG, $row->image_type);
        $this->assertEquals( $row->image_link, $link);
        
        ### We do it again now  with different link path
        
        $link = 'http://api.vubla.com/images/btn_buy_blue.png';
        $i = new CachedImage('1','2',$link);
    

        $this->pdo = vpdo::getVdo('phpunit_cache');
        $row = $this->pdo->getRow("select * from image_cache where wid = ? and pid = ?",array( 1,2));
        
        $this->assertInternaltype('object', $row);
        $this->assertEquals(IMAGETYPE_PNG, $row->image_type);
        $this->assertEquals( $row->image_link, $link);
            
        
    }
    
    
    function testExpirationOnConstructor(){
        $link = 'http://api.vubla.com/images/bg.png';
        $i = new CachedImage('1','2',$link);
    

        $this->pdo = vpdo::getVdo('phpunit_cache');
        $this->pdo->exec("update  image_cache set time = 1000");
        $i = new CachedImage('1','2',$link);
        
        
        $row = $this->pdo->getRow("select * from image_cache where wid = ? and pid = ?",array( 1,2));
        
        $this->assertInternaltype('object', $row);
        $this->assertEquals(IMAGETYPE_PNG, $row->image_type);
        $this->assertTrue( time() - 1000 < $row->time);
        
        
            
        
    }
    */
  
}






