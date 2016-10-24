<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';
new PHPUnit_Framework_TestSuite("ScraperTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);




class ScraperTest extends BaseDbTest 
{
    function setUp() {
        $this->wid = 3;
        $this->builddatabases();
    }
    
    function tearDown() {
        $this->dropdatabases();
    }
    
    function testScrapeError()
    {
        //setup:
        $this->dropdatabases();
        $this->wid = 1; //This test case uses webshop 1 NOT 3
        $this->builddatabases();
        
        //Run:
        try {
            $obj = new MagentoScraper($this->wid);
            $obj->scrape();
            $this->assertFalse(true);
        } catch (VublaException $e)
        {
             $this->assertTrue(true);
        }
        
        
        try {
            $obj = new OscommerceScraper(1);
            $obj->scrape();
            $this->assertFalse(true);
         
        } catch (VublaException $e)
        {
             $this->assertTrue(true);
             
        }
    }
    
    function testScrape()
    {
        settings::setLocal('mage_api_key' ,'45c088765ab907bc65869cba3656fb1b',3);   
        $obj = new MagentoScraper($this->wid);
        $obj->scrape();
        $this->assertGreaterThanOrEqual(0,sizeof(Scraper::$errors));
        // todo: test scraper works properly
       
    }
    
     function testScrapeIdenticalToView()
    {
        scrapemode::set('full');
          $pdo = $this->shopVdo;
          $pdo->exec('truncate table products');
      //       VOB::setTarget(VOB::TARGET_STDOUT);
        settings::setLocal('mage_api_key' ,'45c088765ab907bc65869cba3656fb1b',3); 
        $this->metaVdo->exec("update webshops set hostname = 'magento.crawler.vubla.com' , type = 2");  
        $pdo->exec("update words set rank = 1000");
        $res =  $pdo->fetchOne("select rank from words order by rank desc limit 1");
        $this->assertEquals(1000, $res);
       
        $obj = new MagentoScraper($this->wid);
        $obj->scrape();
        OptionHandler::correctOptionsSettings(3);
        $obj->finish();
       
        $list =  $pdo->getTableList("select * from products");
        $this->assertGreaterThanOrEqual(1,sizeof($list));
        $pdo->exec("CREATE VIEW `product_options_test` AS 
select 
    `options_values`.`product_id` AS `product_id`,
    max((case when (`options_settings`.`r_display_identifier` = 'name') then `options_values`.`name` else NULL end)) AS `name`,
    max((case when (`options_settings`.`r_display_identifier` = 'price') then `options_values`.`name` else NULL end)) AS `price`,
    max((case when (`options_settings`.`r_display_identifier` = 'description') then `options_values`.`name` else NULL end)) AS `description`,
    max((case when (`options_settings`.`r_display_identifier` = 'buy_link') then `options_values`.`name` else NULL end)) AS `buy_link`,
    max((case when (`options_settings`.`r_display_identifier` = 'image_link') then `options_values`.`name` else NULL end)) AS `image_link`,
    max((case when (`options_settings`.`r_display_identifier` = 'link') then `options_values`.`name` else NULL end)) AS `link`,
    max((case when (`options_settings`.`r_display_identifier` = 'pid') then `options_values`.`name` else NULL end)) AS `pid`,
    max((case when (`options_settings`.`r_display_identifier` = 'discount_price') then `options_values`.`name` else NULL end)) AS `discount_price` ,
    max((case when (`options_settings`.`r_display_identifier` = 'lowest_price') then `options_values`.`name` else NULL end)) AS `lowest_price`,
    max((case when (`options_settings`.`r_display_identifier` = 'quantity') then `options_values`.`name` else NULL end)) AS `quantity` ,
    max((case when (`options_settings`.`r_display_identifier` = 'sku') then `options_values`.`name` else NULL end)) AS `sku` 
from 
    ((`options_settings` join `options` 
        on((`options_settings`.`name` = `options`.`name`)))
    join `options_values` 
        on((`options`.`id` = `options_values`.`option_id`))
    )    
group by `options_values`.`product_id`");    
        $list1 =  $pdo->getTableList("select * from product_options_test order by pid");
        $list2 =  $pdo->getTableList("select * from product_options order by pid");
        $this->assertGreaterThanOrEqual(1,sizeof($list1));
        foreach($list1 as $k1 => $v1){
            $this->assertEquals($v1, $list2[$k1], print_r($v1, true).PHP_EOL.PHP_EOL.  print_r($list2[$k1], true));
        }
        $res =  $this->shopVdo->fetchOne("select rank from words order by rank desc limit 1");
        $this->assertEquals(1000, $res);
       
    }
    
  
}
    





?>
