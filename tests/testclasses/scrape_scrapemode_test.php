<?php
require_once '../vublamailer.php';
require_once '../basedbtest.php';
new PHPUnit_Framework_TestSuite("ScrapeModeTest");




class ScrapeModeTest extends BaseDbTest 
{

    function setUp() {
        
    }
    
    function tearDown() {
       
        
    }
    
 
    function testGetSet()
    {
        ScrapeMode::set('update');
        $scrapemode = ScrapeMode::get();
        $this->assertInstanceOf('UpdateScrapeMode', $scrapemode);
        $this->assertEquals('', ScrapeMode::getPF());
        
        ScrapeMode::set('full');
        $scrapemode = ScrapeMode::get();
        $this->assertInstanceOf('FullScrapeMode', $scrapemode);
        $this->assertEquals('_tmp', ScrapeMode::getPF());
        
    }
    
    
    function testConditional()
    {
        ScrapeMode::set('update');
        if(ScrapeMode::get() == 'update')
        {
           $this->assertTrue(true);
        } else {
            $this->assertFalse(true);
        }
    }
 
   
}






