<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';
new PHPUnit_Framework_TestSuite("MagentoSoapClientFactoryTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);

class MagentoSoapClientFactoryMock extends MagentoSoapClientFactory 
{
    protected $hostname;
    
    public function __construct($wid,$hostname)
    {
        parent::__construct($wid);
        $this->hostname = $hostname;
    }
    
    public function getHostname()
    {
        return $this->hostname;
    }
}

class MagentoSoapClientFactoryTest extends BaseDbTest 
{
    protected $wid = 1;
    
    function setUp() 
    {
        $this->wid = 1;
        $this->buildDatabases();
    }
    
    function tearDown() 
    {
        $this->dropDatabases();
    }
    
    function testNoHttpBasicRequired()
    {
        settings::setLocal('mage_api_key','test_api_key',$this->wid);
        $factory = new MagentoSoapClientFactoryMock($this->wid,'magento1.4.0-1.7.x1.7.crawler.vubla.com');
        $this->assertNotNull($factory);
        $client = $factory->create();
        $this->assertNotNull($client);
        $res = $client->call('catalog_product.list');
        $this->assertNotEmpty($res);
    }
    
    // use this test if htaccess is used on magento test shop 1.6 (or just change it to what ever) 
    /*
    function testHttpBasicRequired()
    {
        settings::setLocal('mage_api_key','test_api_key',$this->wid);
        settings::setLocal('http_username','searcher',$this->wid);
        settings::setLocal('http_password','johnErEnN|kkeDukke',$this->wid);
        $factory = new MagentoSoapClientFactoryMock($this->wid,'magento1.4.0-1.7.x1.6.crawler.vubla.com');
        $this->assertNotNull($factory);
        $client = $factory->create();
        $this->assertNotNull($client);
        $res = $client->call('catalog_product.list');
        $this->assertNotEmpty($res);
    }
    */
}
