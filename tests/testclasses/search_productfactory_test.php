<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';



$suite  = new PHPUnit_Framework_TestSuite("ProductFactoryTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);

class ProductFactoryTest extends BaseDbTest 
{
    var $wid = 1;
    function setUp() 
    {
        $this->buildDatabases();
        $this->productFactory = new ProductFactory($this->wid);
        $this->data = array(1,2,3,4,5,6,7,8,9);
    }

    function tearDown() 
    {
        $this->dropDatabases();
    }
    
    function testCorrectOrder() {
        $res = $this->productFactory->getResults($this->data);
        
        $this->assertEquals(sizeof($this->data),sizeof($res));
        for($i = 0 ; $i < sizeof($res) ; $i++){
            $this->assertEquals($this->data[$i],$res[$i]->product_id);
        }
    }
    
    function testCorrectOrder2() {
        $data = array_reverse($this->data);
        $res = $this->productFactory->getResults($data);
        
        $this->assertEquals(sizeof($data),sizeof($res));
        for($i = 0 ; $i < sizeof($res) ;$i++){
            $this->assertEquals($data[$i],$res[$i]->product_id);
        }
    }
    
    function testEmptyInputGivesEmptyOutput() {
        $data = array();
        $res = $this->productFactory->getResults($data);
        
        $this->assertEmpty($res);
    }
    
    function testEveryThingIsSetOnAProduct() {
        Settings::setLocal('use_image_cache',0,$this->wid);
        $data = array(1);
        $res = $this->productFactory->getResults($data);
        $length = Settings::getLocal('description_lenght',$this->wid) - 5;
        
        $this->assertCount(1,$res);
        $this->assertEquals('1',$res[0]->product_id);
        $this->assertEquals('Matrox G200 MMS',$res[0]->name);
        $this->assertEquals('299,99',$res[0]->price);
        $this->assertEquals(substr('Reinforcing its position as a multi-monitor trailblazer, Matrox Graphics Inc. has once again developed the most flexible and highly advanced solution in the industry. Introducing the new Matrox G200 Multi-Monitor Series; the first graphics card ever to support up to four DVI digital flat panel displays on a single 8" PCI board.With continuing demand for digital flat panels in the financial workplace, the Matrox G200 MMS is the ultimate in flexible solutions. The Matrox G200 MMS also supports the new digital video interface (DVI) created by the Digital Display Working Group (DDWG) designed to ease the adoption of digital flat panels. Other configurations include composite video capture ability and onboard TV tuner, making the Matrox G200 MMS the complete solution for business needs.Based on the award-winning MGA-G200 graphics chip, the Matrox G200 Multi-Monitor Series provides superior 2D/3D graphics acceleration to meet the demanding needs of business applications such as real-time stock quotes (Versus), live video feeds (Reuters & Bloombergs), multiple windows applications, word processing, spreadsheets and CAD.',0,$length),
        substr($res[0]->description,0,$length));
        $this->assertEquals('http://everlight.dk/index.php?cPath=3_10&sort=2a&action=buy_now&products_id=1',$res[0]->buy_link);
        $this->assertEquals('http://everlight.dk/images/matrox/mg200mms.gif',$res[0]->image_link);
        $this->assertEquals('http://everlight.dk/product_info.php?products_id=1',$res[0]->link);
        $this->assertEquals('1',$res[0]->pid);
        $this->assertEquals('0,00',$res[0]->discount_price);
        $this->assertEquals('299,99',$res[0]->lowest_price);
    }
    
    function testImageCacheIsUsedOnAProduct() {
        Settings::setLocal('use_image_cache','1',$this->wid);
        $data = array(1);
        $res = $this->productFactory->getResults($data);
        
        $this->assertCount(1,$res);
        $this->assertEquals(API_URL.'/cache/image.php?wid=1&h=f4621395095d74ee95e537c446592ec7&pid=1&image_link=http://everlight.dk/images/matrox/mg200mms.gif'
,$res[0]->image_link);
    }
    
    function testVatIsCorrect() {
        $vat = 2;
        $data = array(3);
        $length = Settings::getLocal('description_lenght',$this->wid) - 5;
        Settings::setLocal('vat_multiplyer',$vat,$this->wid);
        $_GET['vubla_enable_vat'] = 1;
        $res = $this->productFactory->getResults($data);
            
        
        $this->assertCount(1,$res);
        $this->assertEquals('3',$res[0]->product_id);
        $this->assertEquals(implode(',',explode('.',49.99*$vat)),$res[0]->price);
        $this->assertEquals('3',$res[0]->pid);
        $this->assertEquals(implode(',',explode('.',39.99*$vat)),$res[0]->discount_price);
        $this->assertEquals(implode(',',explode('.',39.99*$vat)),$res[0]->lowest_price);
    }
    
    
   function testVatIsDonePropelry()
    {
        $data = array(1);
         $_GET['vubla_enable_vat'] = 1;
        settings::setLocal('vat_multiplyer','1.25',$this->wid);
        settings::setLocal('prices_stored_with_vat','1',$this->wid);
        $res = $this->productFactory->getResults($data);
        $this->assertContains('299,99',$res[0]->price); 
        
        
        settings::setLocal('vat_multiplyer','1.25',$this->wid);
        settings::setLocal('prices_stored_with_vat','0',$this->wid);
        $res = $this->productFactory->getResults($data);
        $this->assertContains('374,9',$res[0]->price); 
   
   
   
   
        
        settings::setLocal('vat_multiplyer','1.25',$this->wid);
        settings::setLocal('prices_stored_with_vat','0',$this->wid);
        $res = $this->productFactory->getResults($data);
        $this->assertContains('374,9',$res[0]->price); 
    
    
    
          $_GET['vubla_enable_vat'] = false;
        settings::setLocal('vat_multiplyer','1.25',$this->wid);
        settings::setLocal('prices_stored_with_vat','0',$this->wid);
        $res = $this->productFactory->getResults($data);
      
        $this->assertContains('299,99',$res[0]->price); 
        
        
        settings::setLocal('vat_multiplyer','1.25',$this->wid);
        settings::setLocal('prices_stored_with_vat','1',$this->wid);
       
        $res = $this->productFactory->getResults($data);
        $this->assertContains('239,99',$res[0]->price); 
        
      
        
   
    }
}

