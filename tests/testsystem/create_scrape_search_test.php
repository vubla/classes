<?php


require_once '../vublamailer.php';
require_once '../basedbtest.php';

$suite = new PHPUnit_Framework_TestSuite("CreateScrapeSearchTest");



/**
 * This test class tests that it is possible to create a new webshop, scrape it and then search on it. 
 * There are sevaral test cases, one for each module.
 */
class CreateScrapeSearchTest extends BaseDbTest {
    
    protected $wid = 1;
    private $out; 
    private $mage_key = '';
    function setUp(){
        $this->buildDatabases();
        
        $db = vpdo::getVdo(null);
        $db->exec('drop database if exists '.DB_PREFIX.'84');
   
        $db->exec('delete from  '.DB_METADATA.'.webshops');
        
    }
    function tearDown(){
            $this->wid = 1;
        $this->dropDatabases();
    }
    
  
    function testOscommerceShopSystem(){
     
        $type = 1;
        $hostname = 'oscommerce2.3.1.crawler.vubla.com';
       
        $this->all($hostname, $type);
        $this->oscommerceResult();
        $this->scrape('update');
        $this->oscommerceResult();
    }
    
    function oscommerceResult(){
        //$this->assertContains('90,00 kr', $this->out);
        $this->assertContains("Disciples: Sacred Lands", $this->out);
        //$this->assertContains('Software', $this->out);
        //$this->assertContains('Strategy', $this->out);
        $this->assertContains('<b>1</b>', $this->out);
       
    }

    function testMagentoShopSystem(){
        $type = 2;
        $hostname = 'magento.crawler.vubla.com';
        $this->mage_key = '45c088765ab907bc65869cba3656fb1b';
        
        $this->all($hostname, $type);
        $this->magentoResult();
        $this->scrape('update');
        $this->magentoResult();
    }
    
    function magentoResult(){
        $this->assertContains('Test produkt crawl 1', $this->out, print_r($this->out, true));
        $this->assertContains('tester helt vildt.', $this->out, print_r($this->out, true));
        //$this->assertContains('det er en sub-kat. test', $this->out);
        $this->assertContains('Vi fandt ', $this->out);
        $this->assertContains('<b>3</b>', $this->out);
        $this->assertContains('Test produkt 2 til', $this->out);
        //$this->assertContains('1.212,00 kr.', $this->out);
        //$this->assertContains('12,00 kr.', $this->out);
        //$this->assertContains('12.421,00 kr.', $this->out);
    }
    
    
    function all($hostname, $type, $mode = 'full',$templatetype = 1 ){
       $this->create($hostname, $type);
       settings::setLocal('mage_api_key',$this->mage_key,$this->wid);
       settings::setLocal('api_out', 0, $this->wid);
       settings::setLocal('ranked_search_threshold', 101, $this->wid);
       $this->scrape($mode);
       $this->search($hostname);
    }
    
    
    /**
     * First part of the test, creating the database
     */
    function create($hostname, $type, $templatetype = 1){
         $dbadmin = new WebshopDbManager();
         //$dbadmin->generate_new($hostname.'@domain.tld',$hostname);
        // public function generate($hostname,'0',$email,$name,$password,$company,$phone,$address,$address2,$postal,$city,$master){
         $dbadmin->generate("'$hostname'","'0'","'$hostname@domain.tld'","''","'qwertyu'","''","''","''","''","''","''","''"); 
         $this->wid = $dbadmin->getWid();
         template::setCurrentTemplate($templatetype,$this->wid);
         WebshopDbManager::setWebshopType($this->wid, $type);
         $db = vpdo::getVdo(DB_PREFIX.$this->wid);
            $db->exec("
drop view product_options;

CREATE VIEW `product_options` AS 
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
    
    }
    
    /**
     * Second part, scraping the webshop. 
     */
    function scrape($mode = 'full'){
        $scrapehandler = new ScrapeHandler($this->wid);
        ScrapeMode::set($mode);
        $scrapehandler->startSafeScraping();
        
    }
    
    /**
     * Third part, searching on the webshop
     */
    function search($hostname, $enabled = 1){
       $_GET['host'] = $hostname;
       $_GET['q'] = 'test';
       $_GET['file'] = 'somefile.php';
       ob_get_clean();
       Settings::setLocal('search_result_output_format','html',$this->wid);
       Settings::setLocal('enabled', $enabled, $this->wid); 
       $this->assertEquals(1, settings::get('enabled',$this->wid));
       ob_start();
       $searchhandler = new SearchHandler(); 
       $this->out = $searchhandler->getOutput();
       $ob = ob_get_contents()   ;
       $this->assertEquals('',$ob);
       $this->assertEquals($this->wid,$searchhandler->wid, "The Searchhandler did not search on the newly created db");
    }
    
}
