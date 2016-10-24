<?php


require_once '../vublamailer.php';
require_once '../basedbtest.php';



new PHPUnit_Framework_TestSuite("MagentoFetcherTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);


class TestMagentoFetcher1 extends MagentoFetcher{
    public $key = "45c088765ab907bc65869cba3656fb1b";
   
       public function __construct($id,$client = null){
            CatalogFetcher::__construct(1);
            $this->wid = 1;
            $this->hostname = "magento.crawler.vubla.com";
            if($client){
                $this->client = $client;
            } else {
                $this->client =  $this->getClient();
            }
         
        }
        
    protected function getClient() {
        if(is_null($this->client)){
             
             $this->client = new MagentoSoapClient($this->getHostname(),$this->key); 
        }
        
        return $this->client;
    }
    function getHostname() {
            return "magento.crawler.vubla.com";
    }
    
   
}

class TestMagentoSoapClient extends MagentoSoapClient
{
    function __construct(){
        
    }
}

class TestScraper1 extends Scraper {
    function getFetcher() { return null; }
}

class MockedMagentoFetcher extends TestMagentoFetcher1 {
    function getProduct($id)
    {
       return  MagentoFetcherTest::getProduct($id);
    }
}
class MagentoFetcherTest extends BaseDbTest 
{

    protected $wid = 1;
	
    function setUp() {
    	
        $this->wid = 1;
        $this->buildDatabases();
     
    }
    
    function tearDown() {
        
        $this->dropDatabases();
    }
    
    function testError()
    {
      //  settings::setLocal('hide_products_out_of_stock', 1, $this->wid);
        VOB::setTarget(VOB::TARGET_NONE);
        try {
            $c = new MagentoFetcher(1);
            $c->getNextProduct();
            $this->assertFalse(true);
        } catch (MagentoLoginException $e){
                
            $this->assertTrue(true);
        }
        
        
      
    }
    
    function testGetNextProduct()
    {
    		  Settings::setLocal('mage_soap_fetch_only_active',0, 1);
          VOB::setTarget(VOB::TARGET_NONE);
        $c = new TestMagentoFetcher1(3);
        $i = 0;
        while($p = $c->getNextProduct()){
            $this->assertInstanceOf('Product', $p);
            if(!$p instanceof EmptyProduct){
                $i++;
            }
            if($i > 10){
                $this->assertFail();
            }
        }
        $this->assertEquals(3, $i);
    }
    
       function testGetNextProductHideActive()
    {
          VOB::setTarget(VOB::TARGET_NONE);
		  Settings::setLocal('mage_soap_fetch_only_active',1, 1);
        $c = new TestMagentoFetcher1(3);
        $i = 0;
        while($p = $c->getNextProduct()){
            $this->assertInstanceOf('Product', $p);
            if(!$p instanceof EmptyProduct){
                $i++;
            }
            if($i > 10){
                $this->assertFail();
            }
        }
        $this->assertEquals(3, $i);
    }
    
   
    
   
    function testGetNextCategory($save = false)
    {
            VOB::setTarget(VOB::TARGET_NONE);
        $cl = new TestMagentoFetcher1(3);
        $i = 0;
        while($c = $cl->getNextCategory()){
            $this->assertInstanceOf('Category', $c);
            $i++;
             if($i > 10){
                $this->assertFail();
            }
            if($save)
            {
                $c->save(1);
            }
            
        }
        $this->assertEquals(3, $i);
    }
	
    static  $first;
    function testUpdateScrape()
    {
       /// We start by scraping the webshop fully. 
       ob_start();
       ScrapeMode::set('full');
       $s = new TestScraper1(1);
       $s->prepare();
 
       $this->testGetNextCategory(true);
    
       self::$first = true;
       
        $client = $this->getMock('TestMagentoSoapClient');
        $client->expects($this->any())
               ->method('call')
               ->will($this->returnValue(array(array('product_id'=>1),array('product_id'=>2))));
         
        
        
        
        $magmock = new MockedMagentoFetcher(1,$client);
        while(($p = $magmock->getNextProduct()) != null)
        {
            $p->save();
        } 
        $s->finish(); 
        /// We have now crawled it regularily, now we check that it worked for double ensurence.
        VOB::setTarget(VOB::TARGET_NONE);
        $_GET['host'] = 'everlight.dk';
        $_GET['q'] = 'test';
        settings::setLocal('api_out', 0, 1);
        $sh = new SearchHandler();
        $res = $sh->getOutput();
        
        $this->assertContains('tester helt vildt',$res, print_r($res,true));
        
        $this->assertNotContains('haster pull kaldt',$res);
      //  $this->assertContains('only product 2', $res);
      
         
         
        // Clean ob and make the mock know we are not in the first run any more.  
        ob_get_clean();
        self::$first = false; 
        
           VOB::setTarget(VOB::TARGET_NONE);
        settings::setLocal('ranked_search_threshold', 101, 1);
        // Go for secund run
        ScrapeMode::set('update');
        /*
        $client = $this->getMock('TestMagentoSoapClient');
        $client->expects($this->any())
               ->method('call')
               ->will($this->returnValue(array(array('product_id'=>2))));
         */
        $cl = new MockedMagentoFetcher(1,$client);
        while(($p = $cl->getNextProduct()) != null)
        {
            $p->save();
        }
         $s->finish(); 
      
        $sh = new SearchHandler();
        $res = $sh->getOutput();
        $this->assertNotContains('tester helt vildt',$res);
        $this->assertContains('tester pull kaldt',$res);
     //   $this->assertContains('only product 2', $res); 
        
        
        // The product has been removed, and we need to check that it does not generate an error. (Current implementation does not delete)
        $_GET['q'] = 'gynge test';  
        $sh = new SearchHandler();
        $res = $sh->getOutput();
        $this->assertNotContains('tester helt vildt',$res);
        $this->assertContains('tester pull kaldt',$res);
      //  $this->assertContains('only product 2', $res);
        
        
    }
    
    static function getProduct($id){
        switch($id){
            case 1:
                return unserialize('a:52:{s:10:"image_link";s:121:"http://magento.crawler.vubla.com/media/catalog/product/h/u/husk_lav_link_til_ny_magento_punkter._lav_install_lidt_om..png";s:3:"pid";s:1:"1";s:3:"sku";s:5:"test1";s:3:"set";s:1:"4";s:4:"type";s:6:"simple";s:10:"categories";a:1:{i:0;s:1:"3";}s:8:"websites";a:1:{i:0;s:1:"1";}s:7:"type_id";s:6:"simple";s:12:"product_name";s:20:"Test produkt crawl 1";s:19:"product_description";s:38:"dette er en lille gynge lavet af test.";s:17:"short_description";s:31:"kort beskrivelse af test-gynge.";s:6:"weight";s:7:"10.0000";s:6:"old_id";N;s:14:"news_from_date";N;s:12:"news_to_date";N;s:6:"status";s:1:"1";s:7:"url_key";s:20:"test-produkt-crawl-1";s:3:"url";s:25:"test-produkt-crawl-1.html";s:10:"visibility";s:1:"4";s:12:"category_ids";a:1:{i:0;s:1:"3";}s:16:"required_options";s:1:"0";s:11:"has_options";s:1:"0";s:11:"image_label";N;s:17:"small_image_label";N;s:15:"thumbnail_label";N;s:10:"created_at";s:19:"2012-02-02 19:57:30";s:10:"updated_at";s:19:"2012-02-14 15:10:56";s:22:"country_of_manufacture";N;s:13:"product_price";s:7:"12.0000";s:14:"discount_price";N;s:17:"special_from_date";N;s:15:"special_to_date";N;s:10:"tier_price";a:0:{}s:13:"minimal_price";N;s:12:"msrp_enabled";s:1:"2";s:30:"msrp_display_actual_price_type";s:1:"4";s:4:"msrp";N;s:21:"enable_googlecheckout";s:1:"1";s:12:"tax_class_id";s:1:"0";s:10:"meta_title";N;s:12:"meta_keyword";N;s:16:"meta_description";N;s:12:"is_recurring";s:1:"0";s:17:"recurring_profile";N;s:13:"custom_design";N;s:18:"custom_design_from";N;s:16:"custom_design_to";N;s:20:"custom_layout_update";N;s:11:"page_layout";N;s:17:"options_container";s:10:"container2";s:22:"gift_message_available";N;s:8:"buy_link";s:76:"http://magento.crawler.vubla.com/index.php/checkout/cart/add?qty=1&product=1";}');
                 break;
            case 2:
                if(self::$first){
                return unserialize('a:52:{s:10:"image_link";s:99:"http://magento.crawler.vubla.com/media/catalog/product/a/p/aptanavfs8112007230282551642btn_send.png";s:3:"pid";s:1:"2";s:3:"sku";s:5:"test2";s:3:"set";s:1:"4";s:4:"type";s:6:"simple";s:10:"categories";a:1:{i:0;s:1:"5";}s:8:"websites";a:1:{i:0;s:1:"1";}s:7:"type_id";s:6:"simple";s:12:"product_name";s:24:"Test produkt 2 til crawl";s:19:"product_description";s:24:"lang beskrivelse af test";s:17:"short_description";s:18:"tester helt vildt.";s:6:"weight";s:7:"12.0000";s:6:"old_id";N;s:14:"news_from_date";N;s:12:"news_to_date";N;s:6:"status";s:1:"1";s:7:"url_key";s:24:"test-produkt-2-til-crawl";s:3:"url";s:29:"test-produkt-2-til-crawl.html";s:10:"visibility";s:1:"4";s:12:"category_ids";a:0:{}s:16:"required_options";s:1:"0";s:11:"has_options";s:1:"0";s:11:"image_label";N;s:17:"small_image_label";N;s:15:"thumbnail_label";N;s:10:"created_at";s:19:"2012-02-02 19:59:53";s:10:"updated_at";s:19:"2012-02-19 12:16:54";s:22:"country_of_manufacture";N;s:13:"product_price";s:9:"1212.0000";s:14:"discount_price";N;s:17:"special_from_date";N;s:15:"special_to_date";N;s:10:"tier_price";a:0:{}s:13:"minimal_price";N;s:12:"msrp_enabled";s:1:"2";s:30:"msrp_display_actual_price_type";s:1:"4";s:4:"msrp";N;s:21:"enable_googlecheckout";s:1:"1";s:12:"tax_class_id";s:1:"0";s:10:"meta_title";N;s:12:"meta_keyword";N;s:16:"meta_description";N;s:12:"is_recurring";s:1:"0";s:17:"recurring_profile";N;s:13:"custom_design";N;s:18:"custom_design_from";N;s:16:"custom_design_to";N;s:20:"custom_layout_update";N;s:11:"page_layout";N;s:17:"options_container";s:10:"container2";s:22:"gift_message_available";N;s:8:"buy_link";s:76:"http://magento.crawler.vubla.com/index.php/checkout/cart/add?qty=1&product=2";}');
                }  

                return unserialize('a:52:{s:10:"image_link";s:99:"http://magento.crawler.vubla.com/media/catalog/product/a/p/aptanavfs8112007230282551642btn_send.png";s:3:"pid";s:1:"2";s:3:"sku";s:5:"test2";s:3:"set";s:1:"4";s:4:"type";s:6:"simple";s:10:"categories";a:1:{i:0;s:1:"5";}s:8:"websites";a:1:{i:0;s:1:"1";}s:7:"type_id";s:6:"simple";s:12:"product_name";s:24:"Test produkt 2 til crawl";s:19:"product_description";s:24:"lang beskrivelse af test";s:17:"short_description";s:18:"tester pull kaldt.";s:6:"weight";s:7:"12.0000";s:6:"old_id";N;s:14:"news_from_date";N;s:12:"news_to_date";N;s:6:"status";s:1:"1";s:7:"url_key";s:24:"test-produkt-2-til-crawl";s:3:"url";s:29:"test-produkt-2-til-crawl.html";s:10:"visibility";s:1:"4";s:12:"category_ids";a:0:{}s:16:"required_options";s:1:"0";s:11:"has_options";s:1:"0";s:11:"image_label";N;s:17:"small_image_label";N;s:15:"thumbnail_label";N;s:10:"created_at";s:19:"2012-02-02 19:59:53";s:10:"updated_at";s:19:"2012-02-19 12:16:54";s:22:"country_of_manufacture";N;s:13:"product_price";s:9:"1212.0000";s:14:"discount_price";N;s:17:"special_from_date";N;s:15:"special_to_date";N;s:10:"tier_price";a:0:{}s:13:"minimal_price";N;s:12:"msrp_enabled";s:1:"2";s:30:"msrp_display_actual_price_type";s:1:"4";s:4:"msrp";N;s:21:"enable_googlecheckout";s:1:"1";s:12:"tax_class_id";s:1:"0";s:10:"meta_title";N;s:12:"meta_keyword";N;s:16:"meta_description";N;s:12:"is_recurring";s:1:"0";s:17:"recurring_profile";N;s:13:"custom_design";N;s:18:"custom_design_from";N;s:16:"custom_design_to";N;s:20:"custom_layout_update";N;s:11:"page_layout";N;s:17:"options_container";s:10:"container2";s:22:"gift_message_available";N;s:8:"buy_link";s:76:"http://magento.crawler.vubla.com/index.php/checkout/cart/add?qty=1&product=2";}');
         
                break;
        }
     }
}
    





