<?php


require_once '../vublamailer.php';
require_once '../basedbtest.php';
new PHPUnit_Framework_TestSuite("CronTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);



class CronTest extends BaseDbTest 
{


    
    function setUp() {
      $this->buildDatabases();
      $this->buildShopDatabase(3);
        
        
    }

  
     
    
    
    function tearDown() {
       $this->dropDatabases();
       $this->dropShopDatabase(3);
     
    }

    function testScrapePerMasterLimit()
    {

        $nighttime = 600500;
        $daytime = 500500;
        settings::setLocal('scrape_master_limit',1, 3); // 3 is master, 1 is slave
        settings::setLocal('scrape_master_night_limit',2, 3); // 3 is master, 1 is slave
        vpdo::getVdo(DB_METADATA)->exec("


truncate table crawllist;

INSERT INTO `crawllist` (`wid`, `last_crawled`, `currentlybeingcrawled`, `email_me`, `last_updated`, `crawl_interval`, `update_interval`, `update_mode_supported`, `scrape_asap`) VALUES
(1, 600000, 0, 0, 600000, 50000, 300, 1,0),
(2, 600000, 0, 0, 600000, 50000, 300, 1,0),
(3, 600001, 0, 0, 600000, 50000, 300, 1,1);


");

        $this->assertEquals(true, Cron::checkMasterScrapeLimit(new Webshop(1),$daytime));
        $this->assertEquals(true, Cron::checkMasterScrapeLimit(new Webshop(1),$nighttime));
        vpdo::getVdo(DB_METADATA)->exec("


truncate table crawllist;

INSERT INTO `crawllist` (`wid`, `last_crawled`, `currentlybeingcrawled`, `email_me`, `last_updated`, `crawl_interval`, `update_interval`, `update_mode_supported`, `scrape_asap`) VALUES
(1, 600000, 0, 0, 600000, 50000, 300, 1,0),
(2, 600001, 1, 0, 600000, 50000, 300, 1,1),
(3, 600001, 0, 0, 600000, 50000, 300, 1,1);


");

        $this->assertEquals(false, Cron::checkMasterScrapeLimit(new Webshop(1),$daytime));
        $this->assertEquals(true, Cron::checkMasterScrapeLimit(new Webshop(1),$nighttime));
        vpdo::getVdo(DB_METADATA)->exec("


truncate table crawllist;

INSERT INTO `crawllist` (`wid`, `last_crawled`, `currentlybeingcrawled`, `email_me`, `last_updated`, `crawl_interval`, `update_interval`, `update_mode_supported`, `scrape_asap`) VALUES
(1, 600000, 0, 0, 600000, 50000, 300, 1,0),
(2, 600001, 0, 0, 600000, 50000, 300, 1,1),
(3, 600001, 1, 0, 600000, 50000, 300, 1,1);


");

        $this->assertEquals(false, Cron::checkMasterScrapeLimit(new Webshop(1),$daytime));
        $this->assertEquals(true, Cron::checkMasterScrapeLimit(new Webshop(1),$nighttime));
        settings::setLocal('scrape_master_limit',0, 3); // 3 is master, 1 is slave
        $this->assertEquals(true, Cron::checkMasterScrapeLimit(new Webshop(1),$daytime));
        settings::setLocal('scrape_master_night_limit',0, 3); // 3 is master, 1 is slave
        $this->assertEquals(true, Cron::checkMasterScrapeLimit(new Webshop(1),$nighttime));

        vpdo::getVdo(DB_METADATA)->exec("


truncate table crawllist;

INSERT INTO `crawllist` (`wid`, `last_crawled`, `currentlybeingcrawled`, `email_me`, `last_updated`, `crawl_interval`, `update_interval`, `update_mode_supported`, `scrape_asap`) VALUES
(1, 600000, 1, 0, 600000, 50000, 300, 1,0),
(2, 600001, 1, 0, 600000, 50000, 300, 1,1),
(3, 600001, 1, 0, 600000, 50000, 300, 1,1);


");
     $this->assertEquals(true, Cron::checkMasterScrapeLimit(new Webshop(2),$daytime));
     $this->assertEquals(true, Cron::checkMasterScrapeLimit(new Webshop(1),$nighttime));

    }
    
    function testFetchLogCron()
    {
           VOB::setTarget(VOB::TARGET_NONE);
        vpdo::getVdo(DB_METADATA)->exec('delete from webshops where id != 1');
        vpdo::getVdo(DB_METADATA)->exec("update webshops set hostname='magento1.4.0-1.7.x1.5.crawler.vubla.com', type=2");
        settings::setLocal('mage_api_key', 'test_api_key',1);
        Cron::fetchLogs();
        $count = vpdo::getVdo(DB_PREFIX.'1')->fetchOne('select rank from words order by rank desc limit 1');
        $this->assertGreaterThan(3,$count);
        Cron::fetchLogs();
        $new_count = vpdo::getVdo(DB_PREFIX.'1')->fetchOne('select rank from words order by rank desc limit 1');
     
    
        
        
        $this->assertEquals($count,$new_count);
    }
  
  function testScrape(){
      ob_start();
    VOB::setTarget(VOB::TARGET_NONE);
vpdo::getVdo(DB_METADATA)->exec("


truncate table crawllist;

INSERT INTO `crawllist` (`wid`, `last_crawled`, `currentlybeingcrawled`, `email_me`, `last_updated`, `crawl_interval`, `update_interval`, `update_mode_supported`, `scrape_asap`) VALUES
(6, 600000, 0, 0, 600000, 50000, 300, 1,0),
(3, 600001, 0, 0, 600000, 50000, 300, 1,1),
(12, 600002, 0, 0, 600000, 50000, 100, 0,0),
(2, 600003, 0, 0, 600400, 50, 500, 1,0),
(5, 600004, 0, 0, 600000, 200, 1, 0,0),
(4, 600005, 1, 0, 600000, 600, 1, 0,0),
(7, 600006, 1, 0, 600000, 100, 1, 0,0),
(8, 600007, 0, 0, 600000, 50000, 100, 1,0);

");
    
      
      
      $this->assertTrue(Cron::isItNight(600500));
      
      cron::scrape(600500, 'TestCrontScrapeHandler');
      
      
        
      $obj = array_shift(TestCrontScrapeHandler::$handlers);
      $this->assertEquals(6, $obj->wid);
      $this->assertInstanceOf('updatescrapemode',$obj->model);
      
      $obj = array_shift(TestCrontScrapeHandler::$handlers);
      $this->assertEquals(3, $obj->wid);
      $this->assertInstanceOf('fullscrapemode',$obj->model);
      
      // 12 get skipped
      
      $obj = array_shift(TestCrontScrapeHandler::$handlers);
      $this->assertEquals(2, $obj->wid);
    //  
      
      $obj = array_shift(TestCrontScrapeHandler::$handlers);
      $this->assertEquals(5, $obj->wid);
      $this->assertInstanceOf('fullscrapemode',$obj->model);
      
      // 4 gets skipped
      
      $obj = array_shift(TestCrontScrapeHandler::$handlers);
      
      $this->assertEquals(7, $obj->wid);
      $this->assertInstanceOf('fullscrapemode',$obj->model);
    
      $obj = array_shift(TestCrontScrapeHandler::$handlers);
      $this->assertEquals(8, $obj->wid);
      $this->assertInstanceOf('updatescrapemode',$obj->model);
       // */
      
  
          
    ob_end_clean();
  }
  
  
  function testScrapeNightEdition(){

vpdo::getVdo(DB_METADATA)->exec("



truncate table crawllist;

INSERT INTO `crawllist` (`wid`, `last_crawled`, `currentlybeingcrawled`, `email_me`, `last_updated`, `crawl_interval`, `update_interval`, `update_mode_supported`, `scrape_asap`) VALUES
(6, 500000, 0, 0, 500000, 50000, 300, 1,0),
(3, 500001, 0, 0, 500000, 50000, 300, 1,1),
(12, 500002, 0, 0, 500000, 50000, 100, 0,0),
(2, 500003, 0, 0, 500400, 50, 500, 1,0),
(5, 500004, 0, 0, 500000, 200, 1, 0,0),
(4, 500005, 1, 0, 500000, 600, 1, 0,0),
(7, 500006, 1, 0, 500000, 100, 1, 0,0),
(8, 500007, 0, 0, 500000, 50000, 100, 1,0);


");
    
       
      $this->assertFalse(Cron::isItNight(500500));
      ob_start();
      Cron::scrape(500500, 'TestCrontScrapeHandler');
      
     
      $obj = array_shift(TestCrontScrapeHandler::$handlers);
      $this->assertEquals(6, $obj->wid);
      $this->assertInstanceOf('updatescrapemode',$obj->model);
      
      
      $obj = array_shift(TestCrontScrapeHandler::$handlers);
      $this->assertEquals(3, $obj->wid);
      $this->assertInstanceOf('fullscrapemode',$obj->model);
      
      $obj = array_shift(TestCrontScrapeHandler::$handlers);
      $this->assertEquals(8, $obj->wid);
      $this->assertInstanceOf('updatescrapemode',$obj->model);
       // */
        ob_end_clean();

  }
      
 

}      

class TestCrontScrapeHandler  {
   static $handlers = array();
   public $model;
   
   public $wid;
   
   function __construct($wid){
       $this->wid = $wid;
   }
   function outputEnabled(){
       
   }
   
   
   protected function getScraper(){
       return $this->getMock('Scraper');
       
   }
   
   function startScraping(){
       $this->model = ScrapeMode::get();
       TestCrontScrapeHandler::$handlers[] = $this;
   }
   
}
    




?>
