<?php


require_once '../vublamailer.php';
require_once '../basedbtest.php';



new PHPUnit_Framework_TestSuite("MagentoAttributeSetTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);

class MagentoAttributeSetTest extends BaseDbTest 
{

    protected $wid = 1;
    
    function setUp() {
         $this->client = new MagentoSoapClient("magento1.4.0-1.7.x1.6.crawler.vubla.com","test_api_key");
        $this->wid = 1;
     //   $this->buildDatabases();
     
    }
    
    function tearDown() {
        MagentoAttributeSet::clear();
        MagentoAttribute::clear();
        $this->client->call(
            "product_attribute.remove",
            array(
                 'new_bool'
            )
        );
       // $this->dropDatabases();
    }
    
    function testLoadOptionLabel()
    {
       
            
         $result = MagentoAttributeSet::get( $this->client,'simple', 38)->getAttribute('color')->getOptionLabel(24);
         $expected = "Black";
         $this->assertEquals($expected, $result);
         
         $result = MagentoAttributeSet::get( $this->client,'simple', 38)->getAttribute('manufacturer')->getOptionLabel(122);
         $expected = "HTC";
         $this->assertEquals($expected, $result);
         
          VOB::setTarget(VOB::TARGET_STDOUT);
          
         $result = MagentoAttributeSet::get( $this->client,'simple', 38)->getAttribute('status')->getOptionLabel(1);
          VOB::setTarget(VOB::TARGET_NONE);
        ob_start();
         $expected = 1;
         $this->assertEquals($expected, $result);
         $this->assertEquals(1,MagentoAttributeSet::get( $this->client,'simple', 38)->timesLoaded);
         $this->assertEquals('', ob_get_clean());
         $result = MagentoAttributeSet::get( $this->client,'simple', 38)->getAttribute('color')->isSearchable();
         
         $expected = true;
         $this->assertEquals($expected, $result);
    }
    
      
    function testLoadOptionLabelNoSets()
    {
        // VOB::setTarget(VOB::TARGET_STDOUT);
            
        $result = MagentoAttribute::get( $this->client, 'color')->getOptionLabel(24);
        $expected = "Black";
        $this->assertEquals($expected, $result);
        
        $result = MagentoAttribute::get( $this->client,'manufacturer')->getOptionLabel(122);
        $expected = "HTC";
        $this->assertEquals($expected, $result);
        
        //Not working set
        $result = MagentoAttribute::get( $this->client,'status')->getOptionLabel(1);
        $expected = 1;
        $this->assertEquals($expected, $result);
       
        $result = MagentoAttribute::get( $this->client,'johnson')->getOptionLabel('aloha');
        $expected = 'aloha';
        $this->assertEquals($expected, $result);
       
        $result = MagentoAttribute::get( $this->client,'sku')->getOptionLabel(750);
        $expected = 750;
        $this->assertEquals($expected, $result);
        
        $result = MagentoAttribute::get( $this->client, 'color')->isSearchable();
        $expected = true;
        $this->assertEquals($expected, $result);
        
        $result = MagentoAttribute::get( $this->client, 'status')->isSearchable();
        $expected = false;
        $this->assertEquals($expected, $result);
    }
    
    
    function testLazyLoad()
    {
        MagentoAttributeSet::get( $this->client,'simple', 38)->vubla = "test";
        $this->assertEquals('test',MagentoAttributeSet::get( $this->client,'simple', 38)->vubla);
        $this->assertEquals(0,MagentoAttributeSet::get( $this->client,'simple', 38)->timesLoaded);
    }
    
   
    function testSupportForOldMagento()
    {
        $this->client = new MagentoSoapClient("magento1.4.0-1.7.x1.4.crawler.vubla.com","test_api_key");
        $result = MagentoAttributeSet::get( $this->client,'simple', 38)->getAttribute('manufacturer')->getOptionLabel(122);
        $expected = "HTC";
        $this->assertEquals($expected, $result);
    }
    
    function testLoadSeveralOptionLabel()
    {
         $result = MagentoAttributeSet::get( $this->client,'simple', 38)->getAttribute('color')->getOptionLabel('23,24,25');
         $expected = "Silver, Black, Blue";
         $this->assertEquals($expected, $result);
    }
    
    
      
    function testLoadBooleanAttribute()
    {
        $id = $this->client->call('product_attribute.create',array('data'=>array(
            'attribute_code' => 'new_bool',
            'frontend_input' => 'boolean',
            'scope' => 'global',
            "default_value" => "1",
            "is_unique" => 0,
            "is_required" => 0,
            'apply_to' => null,
            "is_configurable" => 0,
            'is_searchable' => '1',
            "is_visible_in_advanced_search" => 0,
            "is_used_for_promo_rules" => 0,
            "is_visible_on_front" => 0,
            "used_in_product_listing" => 0,
            'additional_fields' => array(),
            "frontend_label" => array(
                array(
                    "store_id" => 0,
                    "label" => "Boolean attribute"
                )
            )
        )));
        $this->assertNotNull($id);
        $res = $this->client->call('product_attribute_set.attributeAdd',array('attributeId' => $id, 'attributeSetId' => 38));
        $this->assertTrue($res);
        $mock = null;
        foreach ($this->client->call('product_attribute.list',38) as $key => $value) {
            if($value['code'] == 'new_bool')
            {
                $mock = $value;
            }
        }
        $this->assertNotNull($mock);
        
        $result = MagentoAttribute::get( $this->client, 'new_bool',$mock)->getOptionLabel(1);
        $expected = "Boolean attribute";
        $this->assertEquals($expected, $result);

        $result = MagentoAttribute::get( $this->client, 'new_bool')->isSearchable();
        $expected = true;
        $this->assertEquals($expected, $result);
        $result = $this->client->call(
            "product_attribute.remove",
            array(
                 $id
            )
        );
    }
}
    





