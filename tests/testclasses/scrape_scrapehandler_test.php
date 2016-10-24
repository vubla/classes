<?php


require_once '../vublamailer.php';
require_once '../basedbtest.php';


new PHPUnit_Framework_TestSuite("ScrapeHandlerTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);



class ScrapeHandlerTest extends BaseDbTest 
{
	private $client;
    private $api_key = '45c088765ab907bc65869cba3656fb1b';
    private $tempTestProductName;
    function setUp() {
        $this->wid = 3;
        $this->builddatabases();
        $this->shopVdo->exec($this->setUpSql());
         $this->tempTestProductName = "justforupdate";
    }
    
    function tearDown() {
       
       
        if(($this->client))
        {
            $this->client->call( 'catalog_product.delete', array( $this->tempTestProductName),1);
            $this->client = null;
        }
       // $this->client->call( 'catalog_product.delete', array( $this->tempTestProductName),1);
         $this->dropdatabases();
    }
    
    function _createClient()
    {
        return new MagentoSoapClient(vdo::meta()->fetchOne("select hostname from webshops where id = ? ", array(3)),$this->api_key);
    }
    
    function testStartScrapingFailure(){
        //setup:
        $this->dropdatabases();
        $this->wid = 1; //This test case uses webshop 1 NOT 3
        $this->builddatabases();
        
        //Run
        ob_start();
        $obj = new ScrapeHandler($this->wid);
         ScrapeMode::set('full');
        
        try {
            $obj->startSafeScraping();
            $this->assertFail();
        }catch (VublaException $e){
            //Assert
            $this->assertInstanceOf('ScrapeException',$e);
            $this->assertTrue(true);
        }
        ob_end_clean();
        
    }

    function testStartScrapingMagentoShopWithIO(){
           ob_start(); 
        $vdo = vpdo::getVdo(DB_PREFIX.'3');
        settings::setLocal('mage_api_key','45c088765ab907bc65869cba3656fb1b',3);
        
        $obj = new ScrapeHandler($this->wid);
   
        $obj->outputEnabled(false);
        $obj->startSafeScraping();
     
        $res = $vdo->getRowCount('select * from products');
        $this->assertEquals(3,$res);
        $res = $vdo->getRowCount('select * from categories');
        $this->assertEquals(3,$res);
          ob_end_clean();
     
          
    
    
   }
    
    function testStartScrapingMagentoShop(){
        $vdo = $this->shopVdo;
        ob_start();
        
        settings::setLocal('mage_api_key','45c088765ab907bc65869cba3656fb1b',3);
        $obj = new ScrapeHandler($this->wid);
        $obj->outputEnabled(false);
        
        ScrapeMode::set('full');
            /*
        $vdo->exec("update options_settings set importancy = 0 where name = 'color'");
        $res =  $vdo->fetchOne("select importancy from options_settings  where name = 'color'");
        $this->assertEquals(0, $res);
        */
        
        vpdo::getVdo(DB_METADATA)->exec('delete from crawl_log');
        
        $obj->startSafeScraping();
        
      /*  $res =  $vdo->fetchOne("select importancy from options_settings  where name = 'color'");
        $this->assertEquals(1, $res);
        */
     
        $ob = ob_get_clean();  
        $this->assertEquals('',$ob);
        $res = $vdo->getRowCount('select * from products');
        $this->assertEquals(3,$res);
        $res = $vdo->getRowCount('select * from categories');
        $this->assertEquals(3,$res);
      
          // Note in this test pids and prodicts ids are synced. A test of a db where they are not synced must be conducted.
      
        $res = $vdo->fetchOne("select ov.name  from options_values ov inner join options o on ov.option_id = o.id where o.name = 'pid' and product_id = ". 3);
        $this->assertEquals(3, $res);
        $res = $vdo->fetchOne("select pid from product_options where product_id = ". 3);
        $this->assertEquals(3, $res);
   
        //Check that there are no duplicate cats for one product
        $res = $vdo->fetchOne("select count(*) from options_values where product_id = 2 and option_id = (select id from options where name = 'category_id')");
        $this->assertEquals(3, $res);
        
        $res = $vdo->fetchOne("select ov.name  from options_values ov inner join options o on ov.option_id = o.id where o.name = 'pid' and product_id = ". 1);
        $this->assertEquals(1, $res);
        $res = $vdo->fetchOne("select pid from product_options where product_id = ". 1);
        $this->assertEquals(1, $res);

        $sql = 'select scrape_asap from crawllist where wid = ?';
        $scrape_asap_3 = vpdo::getVdo(DB_METADATA)->fetchOne($sql, array($this->wid));
        $this->assertEquals('full', ScrapeMode::get()->__toString());
        $this->assertEquals('1', $scrape_asap_3);
        $obj = new ScrapeHandler($this->wid);
        $obj->outputEnabled(false);
        $obj->startSafeScraping();
        $this->assertEquals('full', ScrapeMode::get()->__toString());
        $scrape_asap_3 = vpdo::getVdo(DB_METADATA)->fetchOne($sql, array($this->wid));
         
        $this->assertEquals('0', $scrape_asap_3);
       
   }


   function testStartScrapingMagentoShopUpdate(){
        $vdo = $this->shopVdo;
        ob_start();
         
        if(is_null($this->client))
        {
            $this->client = $this->_createClient();
        }   
        ScrapeMode::set('full');
        $this->client->call( 'catalog_product.delete', array( $this->tempTestProductName,'SKU'),1);
        $this->testStartScrapingMagentoShop();
        
        
        $res = $vdo->getRowCount('select * from products');
        $this->assertEquals(3,$res);
        settings::setLocal('mage_api_key',$this->api_key,$this->wid);
        $this->client = new MagentoSoapClient(vdo::meta()->fetchOne("select hostname from webshops where id = ? ", array(3)),$this->api_key);
        
      //  $this->client->call('catalog_product.create', array("simple", 4, 'justforupdate', array()))
        if(is_null($this->client))
        {
            $this->client = $this->_createClient();
        }    
        $attributeSets = $this->client->call('product_attribute_set.list');
        $attributeSet = current($attributeSets);


        $result = $this->client->call( 'catalog_product.create', array('simple', $attributeSet['set_id'], $this->tempTestProductName, array(
            'categories' => array(2),
            'websites' => array(1),
            'name' => 'Product name',
            'description' => 'Product description',
            'short_description' => 'Product short description',
            'weight' => '10',
            'status' => '1',
            'url_key' => 'product-url-key',
            'url_path' => 'product-url-path',
            'visibility' => '4',
            'price' => '100',
            'tax_class_id' => 1,
            'meta_title' => 'Product meta title',
            'meta_keyword' => 'Product meta keyword',
            'meta_description' => 'Product meta description'
        )));
        
        $obj = new ScrapeHandler($this->wid);
        $obj->outputEnabled(false);
        
        ScrapeMode::set('update');
            /*
        $vdo->exec("update options_settings set importancy = 0 where name = 'color'");
        $res =  $vdo->fetchOne("select importancy from options_settings  where name = 'color'");
        $this->assertEquals(0, $res);
        */
        
        vpdo::getVdo(DB_METADATA)->exec('delete from crawl_log');
        
        $obj->startSafeScraping();
        $this->client->call( 'catalog_product.delete', array( $this->tempTestProductName,'SKU'),1);
      /*  $res =  $vdo->fetchOne("select importancy from options_settings  where name = 'color'");
        $this->assertEquals(1, $res);
        */
     
        $ob = ob_get_clean();  
        $this->assertEquals('',$ob);
        $res = $vdo->getRowCount('select * from products');
        $this->assertEquals(4,$res);
      
       
   }

/*
function testStartScrapingMagentoShopUpdate14(){
        $vdo = $this->shopVdo;
        ob_start();
        ScrapeMode::set('full');
          vdo::meta()->exec("update webshops set hostname = 'magento1.4.0-1.7.x1.4.crawler.vubla.com'");
        $this->api_key = "test_api_key";
        settings::setLocal('mage_api_key',$this->api_key,3);
        $this->wid = 3;
     
            $this->client = $this->_createClient();
        
        $this->client->call( 'catalog_product.delete', array( $this->tempTestProductName,'SKU'),1);
        

        $obj = new ScrapeHandler($this->wid);
        $obj->outputEnabled(false);
        
        ScrapeMode::set('full');
         
        vpdo::getVdo(DB_METADATA)->exec('delete from crawl_log');
        
        $obj->startSafeScraping();
       
        $res = $vdo->getRowCount('select * from products');
        $this->assertEquals(61,$res);
        settings::setLocal('mage_api_key',$this->api_key,$this->wid);
        $this->client = new MagentoSoapClient(vdo::meta()->fetchOne("select hostname from webshops where id = ? ", array(3)),$this->api_key);
        
      //  $this->client->call('catalog_product.create', array("simple", 4, 'justforupdate', array()))
        if(is_null($this->client))
        {
            $this->client = $this->_createClient();
        }    
        $attributeSets = $this->client->call('product_attribute_set.list');
        $attributeSet = current($attributeSets);


        $result = $this->client->call( 'catalog_product.create', array('simple', $attributeSet['set_id'], $this->tempTestProductName, array(
            'categories' => array(2),
            'websites' => array(1),
            'name' => 'Product name',
            'description' => 'Product description',
            'short_description' => 'Product short description',
            'weight' => '10',
            'status' => '1',
            'url_key' => 'product-url-key',
            'url_path' => 'product-url-path',
            'visibility' => '4',
            'price' => '100',
            'tax_class_id' => 1,
            'meta_title' => 'Product meta title',
            'meta_keyword' => 'Product meta keyword',
            'meta_description' => 'Product meta description'
        )));
        
        $obj = new ScrapeHandler($this->wid);
        $obj->outputEnabled(false);
        
        ScrapeMode::set('update');
      
        
        vpdo::getVdo(DB_METADATA)->exec('delete from crawl_log');
        
        $obj->startSafeScraping();
        $this->client->call( 'catalog_product.delete', array( $this->tempTestProductName,'SKU'),1);
    
     
        $ob = ob_get_clean();  
        $this->assertEquals('',$ob);
        $res = $vdo->getRowCount('select * from products');
        $this->assertEquals(62,$res);
      
       
   }

  */
   function setUpSql(){
       $sql = "truncate table products; truncate table categories;";
       return $sql;
   }
    

}

?>
