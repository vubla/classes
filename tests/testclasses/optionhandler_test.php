<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';


$suite  = new PHPUnit_Framework_TestSuite("OptionHandlerTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);


if(!class_exists("TestScraper"))
{
    class TestScraper extends Scraper{
        function getFetcher(){
          
        }
    }
}
class OptionHandlerTest extends BaseDbTest 
{
    private $test_max_prod;
    private $initial;
    
    function setUp() {
        $this->buildDatabases();
    }
    
    function tearDown() {
        $this->dropDatabases();
    }
    
    function testGetSortableOptions()
    {
        $result = OptionHandler::getSortableOptions(1);
        $this->assertCount(4, $result);
        $this->assertEquals('lowest_price', $result[0]->name);
        $this->assertEquals('lowest_price', $result[1]->name);
        $this->assertEquals('asc', $result[0]->order_by);
        $this->assertEquals('desc', $result[1]->order_by);
        $this->assertEquals('name', $result[2]->name);
        $this->assertEquals('name', $result[3]->name);
        $this->assertEquals('asc', $result[2]->order_by);
        $this->assertEquals('desc', $result[3]->order_by);
    }
    
    
    function testMade4menSettings ()
    {
        
          $wid = 1;
      //  define('WID',1);
      $db = vpdo::getVdo(DB_PREFIX.$wid);
      $db->exec("truncate table options_settings");
      ScrapeMode::set('full');
      $scrape = new TestScraper(1);
      $scrape->prepare();
      
      
        $db->exec("insert into options_tmp (name) values (".$db->quote("aw_os_category_display").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("aw_os_category_image_path").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("aw_os_category_position").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("aw_os_product_display").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("aw_os_product_image_path").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("aw_os_product_position").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("buy_link").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("category").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("category_id").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("created_at").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("default_supply_delay").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("enable_googlecheckout").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("has_options").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("image_label").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("image_link").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("is_in_stock").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("lowest_price").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("manufacturer").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("meta_description").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("meta_title").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("options_container").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("pid").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("product_description").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("product_name").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("product_price").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("required_options").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("set").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("short_description").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("sku").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("small_image_label").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("status").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("tax_class_id").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("thumbnail_label").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("type").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("type_id").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("updated_at").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("url").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("url_key").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("visibility").")");
$case = OptionHandler::createOptionsSetting("aw_os_category_display",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("aw_os_category_image_path",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("aw_os_category_position",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("aw_os_product_display",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("aw_os_product_image_path",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("aw_os_product_position",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("buy_link",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("category",3); $case->save($wid);
$case = OptionHandler::createOptionsSetting("category_id",3); $case->save($wid);
$case = OptionHandler::createOptionsSetting("created_at",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("default_supply_delay",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("enable_googlecheckout",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("has_options",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("image_label",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("image_link",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("is_in_stock",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("lowest_price",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("manufacturer",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("meta_description",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("meta_title",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("options_container",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("pid",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("product_description",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("product_name",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("product_price",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("required_options",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("set",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("short_description",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("sku",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("small_image_label",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("status",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("tax_class_id",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("thumbnail_label",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("type",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("type_id",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("updated_at",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("url",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("url_key",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("visibility",1); $case->save($wid);
OptionHandler::correctOptionsSettings($wid);
$oset = new  OptionsSettingSet($wid); $oset->fillFromDb( "order by name asc");
$case = $oset->shift(); $this->assertEquals("aw_os_category_display", $case->name); $this->assertEquals("not", $case->sortable,"With name: aw_os_category_display");$this->assertEquals("0", $case->importancy,"With name: aw_os_category_display");$this->assertEquals("", $case->r_display_identifier,"With name: aw_os_category_display");$this->assertEquals("", $case->facet_type,"With name: aw_os_category_display");
$case = $oset->shift(); $this->assertEquals("aw_os_category_image_path", $case->name); $this->assertEquals("not", $case->sortable,"With name: aw_os_category_image_path");$this->assertEquals("0", $case->importancy,"With name: aw_os_category_image_path");$this->assertEquals("", $case->r_display_identifier,"With name: aw_os_category_image_path");$this->assertEquals("", $case->facet_type,"With name: aw_os_category_image_path");
$case = $oset->shift(); $this->assertEquals("aw_os_category_position", $case->name); $this->assertEquals("not", $case->sortable,"With name: aw_os_category_position");$this->assertEquals("0", $case->importancy,"With name: aw_os_category_position");$this->assertEquals("", $case->r_display_identifier,"With name: aw_os_category_position");$this->assertEquals("", $case->facet_type,"With name: aw_os_category_position");
$case = $oset->shift(); $this->assertEquals("aw_os_product_display", $case->name); $this->assertEquals("not", $case->sortable,"With name: aw_os_product_display");$this->assertEquals("0", $case->importancy,"With name: aw_os_product_display");$this->assertEquals("", $case->r_display_identifier,"With name: aw_os_product_display");$this->assertEquals("", $case->facet_type,"With name: aw_os_product_display");
$case = $oset->shift(); $this->assertEquals("aw_os_product_image_path", $case->name); $this->assertEquals("not", $case->sortable,"With name: aw_os_product_image_path");$this->assertEquals("0", $case->importancy,"With name: aw_os_product_image_path");$this->assertEquals("", $case->r_display_identifier,"With name: aw_os_product_image_path");$this->assertEquals("", $case->facet_type,"With name: aw_os_product_image_path");
$case = $oset->shift(); $this->assertEquals("aw_os_product_position", $case->name); $this->assertEquals("not", $case->sortable,"With name: aw_os_product_position");$this->assertEquals("0", $case->importancy,"With name: aw_os_product_position");$this->assertEquals("", $case->r_display_identifier,"With name: aw_os_product_position");$this->assertEquals("", $case->facet_type,"With name: aw_os_product_position");
$case = $oset->shift(); $this->assertEquals("buy_link", $case->name); $this->assertEquals("not", $case->sortable,"With name: buy_link");$this->assertEquals("0", $case->importancy,"With name: buy_link");$this->assertEquals("buy_link", $case->r_display_identifier,"With name: buy_link");$this->assertEquals("", $case->facet_type,"With name: buy_link");
$case = $oset->shift(); $this->assertEquals("category", $case->name); $this->assertEquals("not", $case->sortable,"With name: category");$this->assertEquals("2", $case->importancy,"With name: category");$this->assertEquals("", $case->r_display_identifier,"With name: category");$this->assertEquals("", $case->facet_type,"With name: category");
$case = $oset->shift(); $this->assertEquals("category_id", $case->name); $this->assertEquals("not", $case->sortable,"With name: category_id");$this->assertEquals("0", $case->importancy,"With name: category_id");$this->assertEquals("", $case->r_display_identifier,"With name: category_id");$this->assertEquals("", $case->facet_type,"With name: category_id");
$case = $oset->shift(); $this->assertEquals("created_at", $case->name); $this->assertEquals("not", $case->sortable,"With name: created_at");$this->assertEquals("0", $case->importancy,"With name: created_at");$this->assertEquals("", $case->r_display_identifier,"With name: created_at");$this->assertEquals("", $case->facet_type,"With name: created_at");
$case = $oset->shift(); $this->assertEquals("default_supply_delay", $case->name); $this->assertEquals("not", $case->sortable,"With name: default_supply_delay");$this->assertEquals("0", $case->importancy,"With name: default_supply_delay");$this->assertEquals("", $case->r_display_identifier,"With name: default_supply_delay");$this->assertEquals("", $case->facet_type,"With name: default_supply_delay");
$case = $oset->shift(); $this->assertEquals("enable_googlecheckout", $case->name); $this->assertEquals("not", $case->sortable,"With name: enable_googlecheckout");$this->assertEquals("0", $case->importancy,"With name: enable_googlecheckout");$this->assertEquals("", $case->r_display_identifier,"With name: enable_googlecheckout");$this->assertEquals("", $case->facet_type,"With name: enable_googlecheckout");
$case = $oset->shift(); $this->assertEquals("has_options", $case->name); $this->assertEquals("not", $case->sortable,"With name: has_options");$this->assertEquals("0", $case->importancy,"With name: has_options");$this->assertEquals("", $case->r_display_identifier,"With name: has_options");$this->assertEquals("", $case->facet_type,"With name: has_options");
$case = $oset->shift(); $this->assertEquals("image_label", $case->name); $this->assertEquals("not", $case->sortable,"With name: image_label");$this->assertEquals("1", $case->importancy,"With name: image_label");$this->assertEquals("", $case->r_display_identifier,"With name: image_label");$this->assertEquals("", $case->facet_type,"With name: image_label");
$case = $oset->shift(); $this->assertEquals("image_link", $case->name); $this->assertEquals("not", $case->sortable,"With name: image_link");$this->assertEquals("0", $case->importancy,"With name: image_link");$this->assertEquals("image_link", $case->r_display_identifier,"With name: image_link");$this->assertEquals("", $case->facet_type,"With name: image_link");
$case = $oset->shift(); $this->assertEquals("is_in_stock", $case->name); $this->assertEquals("not", $case->sortable,"With name: is_in_stock");$this->assertEquals("0", $case->importancy,"With name: is_in_stock");$this->assertEquals("quantity", $case->r_display_identifier,"With name: is_in_stock");$this->assertEquals("", $case->facet_type,"With name: is_in_stock");
$case = $oset->shift(); $this->assertEquals("lowest_price", $case->name); $this->assertEquals("number", $case->sortable,"With name: lowest_price");$this->assertEquals("0", $case->importancy,"With name: lowest_price");$this->assertEquals("lowest_price", $case->r_display_identifier,"With name: lowest_price");$this->assertEquals("", $case->facet_type,"With name: lowest_price");
$case = $oset->shift(); $this->assertEquals("manufacturer", $case->name); $this->assertEquals("not", $case->sortable,"With name: manufacturer");$this->assertEquals("0", $case->importancy,"With name: manufacturer");$this->assertEquals("", $case->r_display_identifier,"With name: manufacturer");$this->assertEquals("", $case->facet_type,"With name: manufacturer");
$case = $oset->shift(); $this->assertEquals("meta_description", $case->name); $this->assertEquals("not", $case->sortable,"With name: meta_description");$this->assertEquals("1", $case->importancy,"With name: meta_description");$this->assertEquals("", $case->r_display_identifier,"With name: meta_description");$this->assertEquals("", $case->facet_type,"With name: meta_description");
$case = $oset->shift(); $this->assertEquals("meta_title", $case->name); $this->assertEquals("not", $case->sortable,"With name: meta_title");$this->assertEquals("2", $case->importancy,"With name: meta_title");$this->assertEquals("", $case->r_display_identifier,"With name: meta_title");$this->assertEquals("", $case->facet_type,"With name: meta_title");
$case = $oset->shift(); $this->assertEquals("options_container", $case->name); $this->assertEquals("not", $case->sortable,"With name: options_container");$this->assertEquals("0", $case->importancy,"With name: options_container");$this->assertEquals("", $case->r_display_identifier,"With name: options_container");$this->assertEquals("", $case->facet_type,"With name: options_container");
$case = $oset->shift(); $this->assertEquals("pid", $case->name); $this->assertEquals("not", $case->sortable,"With name: pid");$this->assertEquals("0", $case->importancy,"With name: pid");$this->assertEquals("pid", $case->r_display_identifier,"With name: pid");$this->assertEquals("", $case->facet_type,"With name: pid");
$case = $oset->shift(); $this->assertEquals("product_description", $case->name); $this->assertEquals("not", $case->sortable,"With name: product_description");$this->assertEquals("1", $case->importancy,"With name: product_description");$this->assertEquals("", $case->r_display_identifier,"With name: product_description");$this->assertEquals("", $case->facet_type,"With name: product_description");
$case = $oset->shift(); $this->assertEquals("product_name", $case->name); $this->assertEquals("string", $case->sortable,"With name: product_name");$this->assertEquals("3", $case->importancy,"With name: product_name");$this->assertEquals("name", $case->r_display_identifier,"With name: product_name");$this->assertEquals("", $case->facet_type,"With name: product_name");
$case = $oset->shift(); $this->assertEquals("product_price", $case->name); $this->assertEquals("not", $case->sortable,"With name: product_price");$this->assertEquals("0", $case->importancy,"With name: product_price");$this->assertEquals("price", $case->r_display_identifier,"With name: product_price");$this->assertEquals("", $case->facet_type,"With name: product_price");
$case = $oset->shift(); $this->assertEquals("required_options", $case->name); $this->assertEquals("not", $case->sortable,"With name: required_options");$this->assertEquals("0", $case->importancy,"With name: required_options");$this->assertEquals("", $case->r_display_identifier,"With name: required_options");$this->assertEquals("", $case->facet_type,"With name: required_options");
$case = $oset->shift(); $this->assertEquals("set", $case->name); $this->assertEquals("not", $case->sortable,"With name: set");$this->assertEquals("0", $case->importancy,"With name: set");$this->assertEquals("", $case->r_display_identifier,"With name: set");$this->assertEquals("", $case->facet_type,"With name: set");
$case = $oset->shift(); $this->assertEquals("short_description", $case->name); $this->assertEquals("not", $case->sortable,"With name: short_description");$this->assertEquals("1", $case->importancy,"With name: short_description");$this->assertEquals("description", $case->r_display_identifier,"With name: short_description");$this->assertEquals("", $case->facet_type,"With name: short_description");
$case = $oset->shift(); $this->assertEquals("sku", $case->name); $this->assertEquals("not", $case->sortable,"With name: sku");$this->assertEquals("3", $case->importancy,"With name: sku");$this->assertEquals("sku", $case->r_display_identifier,"With name: sku");$this->assertEquals("", $case->facet_type,"With name: sku");
$case = $oset->shift(); $this->assertEquals("small_image_label", $case->name); $this->assertEquals("not", $case->sortable,"With name: small_image_label");$this->assertEquals("0", $case->importancy,"With name: small_image_label");$this->assertEquals("", $case->r_display_identifier,"With name: small_image_label");$this->assertEquals("", $case->facet_type,"With name: small_image_label");
$case = $oset->shift(); $this->assertEquals("status", $case->name); $this->assertEquals("not", $case->sortable,"With name: status");$this->assertEquals("0", $case->importancy,"With name: status");$this->assertEquals("", $case->r_display_identifier,"With name: status");$this->assertEquals("", $case->facet_type,"With name: status");
$case = $oset->shift(); $this->assertEquals("tax_class_id", $case->name); $this->assertEquals("not", $case->sortable,"With name: tax_class_id");$this->assertEquals("0", $case->importancy,"With name: tax_class_id");$this->assertEquals("", $case->r_display_identifier,"With name: tax_class_id");$this->assertEquals("", $case->facet_type,"With name: tax_class_id");
$case = $oset->shift(); $this->assertEquals("thumbnail_label", $case->name); $this->assertEquals("not", $case->sortable,"With name: thumbnail_label");$this->assertEquals("0", $case->importancy,"With name: thumbnail_label");$this->assertEquals("", $case->r_display_identifier,"With name: thumbnail_label");$this->assertEquals("", $case->facet_type,"With name: thumbnail_label");
$case = $oset->shift(); $this->assertEquals("type", $case->name); $this->assertEquals("not", $case->sortable,"With name: type");$this->assertEquals("0", $case->importancy,"With name: type");$this->assertEquals("", $case->r_display_identifier,"With name: type");$this->assertEquals("", $case->facet_type,"With name: type");
$case = $oset->shift(); $this->assertEquals("type_id", $case->name); $this->assertEquals("not", $case->sortable,"With name: type_id");$this->assertEquals("0", $case->importancy,"With name: type_id");$this->assertEquals("", $case->r_display_identifier,"With name: type_id");$this->assertEquals("", $case->facet_type,"With name: type_id");
$case = $oset->shift(); $this->assertEquals("updated_at", $case->name); $this->assertEquals("not", $case->sortable,"With name: updated_at");$this->assertEquals("0", $case->importancy,"With name: updated_at");$this->assertEquals("", $case->r_display_identifier,"With name: updated_at");$this->assertEquals("", $case->facet_type,"With name: updated_at");
$case = $oset->shift(); $this->assertEquals("url", $case->name); $this->assertEquals("not", $case->sortable,"With name: url");$this->assertEquals("0", $case->importancy,"With name: url");$this->assertEquals("link", $case->r_display_identifier,"With name: url");$this->assertEquals("", $case->facet_type,"With name: url");
$case = $oset->shift(); $this->assertEquals("url_key", $case->name); $this->assertEquals("not", $case->sortable,"With name: url_key");$this->assertEquals("0", $case->importancy,"With name: url_key");$this->assertEquals("", $case->r_display_identifier,"With name: url_key");$this->assertEquals("", $case->facet_type,"With name: url_key");
$case = $oset->shift(); $this->assertEquals("visibility", $case->name); $this->assertEquals("not", $case->sortable,"With name: visibility");$this->assertEquals("0", $case->importancy,"With name: visibility");$this->assertEquals("", $case->r_display_identifier,"With name: visibility");$this->assertEquals("", $case->facet_type,"With name: visibility");
    }

    function testCorrectOptionsSetting(){
        // This is an automated integration and acceptance test. It is generated by the following sql: 
        
        /*
   
 
         select concat('$db->exec("insert into options_tmp (name) values (".$db->quote("', name, '").")");') as q 

from (SELECT  o.name, count(*) as c, os.sortable FROM options o inner join `options_values`  ov on ov.option_id = o.id  inner join options_settings os on o.name = os.name where `product_id` =  2 group by o.id order by o.name asc) as n

union


         select concat('$case = OptionHandler::createOptionsSetting("', name, '",', c, '); $case-\>save($wid);') as q 

from (SELECT  o.name, count(*) as c, os.sortable FROM options o inner join `options_values`  ov on ov.option_id = o.id  inner join options_settings os on o.name = os.name where `product_id` =  2 group by o.id order by o.name asc) as n

union

select 'OptionHandler::correctOptionsSettings($wid);'

union 
select '$oset = new  OptionsSettingSet($wid); $oset->fillFromDb( "order by name asc");'

union

select concat('$case = $oset->shift(); $this->assertEquals("',osname, '", $case->name); $this->assertEquals("', sortable, '", $case->sortable,"With name: ',osname,'");$this->assertEquals("', importancy, '", $case->importancy,"With name: ',osname,'");$this->assertEquals("', r_display_identifier, '", $case->r_display_identifier,"With name: ',osname,'");$this->assertEquals("',facet_type, '", $case->facet_type,"With name: ',osname,'");'
) as q 


from (SELECT  o.name, count(*) as c, os.sortable, os.importancy, os.facet_type, os.r_display_identifier, os.name as osname FROM options o inner join `options_values`  ov on ov.option_id = o.id  inner join options_settings os on o.name = os.name where `product_id` =  2 group by o.id order by o.name asc) as n1 
                   */
        
        
     
        
      /// Following not autogenerated   
      $wid = 1;
      //  define('WID',1);
      $db = vpdo::getVdo(DB_PREFIX.$wid);
      $db->exec("truncate table options_settings");
      ScrapeMode::set('full');
      $scrape = new TestScraper(1);
      $scrape->prepare();

      
// Begin autogenerated   
$db->exec("insert into options_tmp (name) values (".$db->quote("aw_os_category_position").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("aw_os_product_position").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("buy_link").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("category").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("category_id").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("cost").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("created_at").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("default_supply_delay").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("enable_googlecheckout").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("image_link").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("is_in_stock").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("lowest_price").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("manufacturer").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("meta_description").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("meta_title").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("options_container").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("pid").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("product_description").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("product_name").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("product_price").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("set").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("short_description").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("sku").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("status").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("tax_class_id").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("type").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("type_id").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("updated_at").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("url").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("url_key").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("visibility").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("weight").")");
$case = OptionHandler::createOptionsSetting("aw_os_category_position",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("aw_os_product_position",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("buy_link",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("category",2); $case->save($wid);
$case = OptionHandler::createOptionsSetting("category_id",2); $case->save($wid);
$case = OptionHandler::createOptionsSetting("cost",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("created_at",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("default_supply_delay",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("enable_googlecheckout",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("image_link",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("is_in_stock",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("lowest_price",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("manufacturer",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("meta_description",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("meta_title",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("options_container",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("pid",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("product_description",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("product_name",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("product_price",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("set",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("short_description",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("sku",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("status",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("tax_class_id",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("type",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("type_id",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("updated_at",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("url",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("url_key",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("visibility",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("weight",1); $case->save($wid);
OptionHandler::correctOptionsSettings($wid);
$oset = new OptionsSettingSet($wid); $oset->fillFromDb( "order by name asc");
$case = $oset->shift(); $this->assertEquals("aw_os_category_position", $case->name); $this->assertEquals("not", $case->sortable,"With name: aw_os_category_position");$this->assertEquals("0", $case->importancy,"With name: aw_os_category_position");$this->assertEquals("", $case->r_display_identifier,"With name: aw_os_category_position");$this->assertEquals("", $case->facet_type,"With name: aw_os_category_position");
$case = $oset->shift(); $this->assertEquals("aw_os_product_position", $case->name); $this->assertEquals("not", $case->sortable,"With name: aw_os_product_position");$this->assertEquals("0", $case->importancy,"With name: aw_os_product_position");$this->assertEquals("", $case->r_display_identifier,"With name: aw_os_product_position");$this->assertEquals("", $case->facet_type,"With name: aw_os_product_position");
$case = $oset->shift(); $this->assertEquals("buy_link", $case->name); $this->assertEquals("not", $case->sortable,"With name: buy_link");$this->assertEquals("0", $case->importancy,"With name: buy_link");$this->assertEquals("buy_link", $case->r_display_identifier,"With name: buy_link");$this->assertEquals("", $case->facet_type,"With name: buy_link");
$case = $oset->shift(); $this->assertEquals("category", $case->name); $this->assertEquals("not", $case->sortable,"With name: category");$this->assertEquals("2", $case->importancy,"With name: category");$this->assertEquals("", $case->r_display_identifier,"With name: category");$this->assertEquals("", $case->facet_type,"With name: category");
$case = $oset->shift(); $this->assertEquals("category_id", $case->name); $this->assertEquals("not", $case->sortable,"With name: category_id");$this->assertEquals("0", $case->importancy,"With name: category_id");$this->assertEquals("", $case->r_display_identifier,"With name: category_id");$this->assertEquals("", $case->facet_type,"With name: category_id");
$case = $oset->shift(); $this->assertEquals("cost", $case->name); $this->assertEquals("not", $case->sortable,"With name: cost");$this->assertEquals("0", $case->importancy,"With name: cost");$this->assertEquals("", $case->r_display_identifier,"With name: cost");$this->assertEquals("", $case->facet_type,"With name: cost");
$case = $oset->shift(); $this->assertEquals("created_at", $case->name); $this->assertEquals("not", $case->sortable,"With name: created_at");$this->assertEquals("0", $case->importancy,"With name: created_at");$this->assertEquals("", $case->r_display_identifier,"With name: created_at");$this->assertEquals("", $case->facet_type,"With name: created_at");
$case = $oset->shift(); $this->assertEquals("default_supply_delay", $case->name); $this->assertEquals("not", $case->sortable,"With name: default_supply_delay");$this->assertEquals("0", $case->importancy,"With name: default_supply_delay");$this->assertEquals("", $case->r_display_identifier,"With name: default_supply_delay");$this->assertEquals("", $case->facet_type,"With name: default_supply_delay");
$case = $oset->shift(); $this->assertEquals("enable_googlecheckout", $case->name); $this->assertEquals("not", $case->sortable,"With name: enable_googlecheckout");$this->assertEquals("0", $case->importancy,"With name: enable_googlecheckout");$this->assertEquals("", $case->r_display_identifier,"With name: enable_googlecheckout");$this->assertEquals("", $case->facet_type,"With name: enable_googlecheckout");
$case = $oset->shift(); $this->assertEquals("image_link", $case->name); $this->assertEquals("not", $case->sortable,"With name: image_link");$this->assertEquals("0", $case->importancy,"With name: image_link");$this->assertEquals("image_link", $case->r_display_identifier,"With name: image_link");$this->assertEquals("", $case->facet_type,"With name: image_link");
$case = $oset->shift(); $this->assertEquals("is_in_stock", $case->name); $this->assertEquals("not", $case->sortable,"With name: is_in_stock");$this->assertEquals("0", $case->importancy,"With name: is_in_stock");$this->assertEquals("quantity", $case->r_display_identifier,"With name: is_in_stock");$this->assertEquals("", $case->facet_type,"With name: is_in_stock");
$case = $oset->shift(); $this->assertEquals("lowest_price", $case->name); $this->assertEquals("number", $case->sortable,"With name: lowest_price");$this->assertEquals("0", $case->importancy,"With name: lowest_price");$this->assertEquals("lowest_price", $case->r_display_identifier,"With name: lowest_price");$this->assertEquals("", $case->facet_type,"With name: lowest_price");
$case = $oset->shift(); $this->assertEquals("manufacturer", $case->name); $this->assertEquals("not", $case->sortable,"With name: manufacturer");$this->assertEquals("0", $case->importancy,"With name: manufacturer");$this->assertEquals("", $case->r_display_identifier,"With name: manufacturer");$this->assertEquals("", $case->facet_type,"With name: manufacturer");
$case = $oset->shift(); $this->assertEquals("meta_description", $case->name); $this->assertEquals("not", $case->sortable,"With name: meta_description");$this->assertEquals("1", $case->importancy,"With name: meta_description");$this->assertEquals("", $case->r_display_identifier,"With name: meta_description");$this->assertEquals("", $case->facet_type,"With name: meta_description");
$case = $oset->shift(); $this->assertEquals("meta_title", $case->name); $this->assertEquals("not", $case->sortable,"With name: meta_title");$this->assertEquals("2", $case->importancy,"With name: meta_title");$this->assertEquals("", $case->r_display_identifier,"With name: meta_title");$this->assertEquals("", $case->facet_type,"With name: meta_title");
$case = $oset->shift(); $this->assertEquals("options_container", $case->name); $this->assertEquals("not", $case->sortable,"With name: options_container");$this->assertEquals("0", $case->importancy,"With name: options_container");$this->assertEquals("", $case->r_display_identifier,"With name: options_container");$this->assertEquals("", $case->facet_type,"With name: options_container");
$case = $oset->shift(); $this->assertEquals("pid", $case->name); $this->assertEquals("not", $case->sortable,"With name: pid");$this->assertEquals("0", $case->importancy,"With name: pid");$this->assertEquals("pid", $case->r_display_identifier,"With name: pid");$this->assertEquals("", $case->facet_type,"With name: pid");
$case = $oset->shift(); $this->assertEquals("product_description", $case->name); $this->assertEquals("not", $case->sortable,"With name: product_description");$this->assertEquals("1", $case->importancy,"With name: product_description");$this->assertEquals("", $case->r_display_identifier,"With name: product_description");$this->assertEquals("", $case->facet_type,"With name: product_description");
$case = $oset->shift(); $this->assertEquals("product_name", $case->name); $this->assertEquals("string", $case->sortable,"With name: product_name");$this->assertEquals("3", $case->importancy,"With name: product_name");$this->assertEquals("name", $case->r_display_identifier,"With name: product_name");$this->assertEquals("", $case->facet_type,"With name: product_name");
$case = $oset->shift(); $this->assertEquals("product_price", $case->name); $this->assertEquals("not", $case->sortable,"With name: product_price");$this->assertEquals("0", $case->importancy,"With name: product_price");$this->assertEquals("price", $case->r_display_identifier,"With name: product_price");$this->assertEquals("", $case->facet_type,"With name: product_price");
$case = $oset->shift(); $this->assertEquals("set", $case->name); $this->assertEquals("not", $case->sortable,"With name: set");$this->assertEquals("0", $case->importancy,"With name: set");$this->assertEquals("", $case->r_display_identifier,"With name: set");$this->assertEquals("", $case->facet_type,"With name: set");
$case = $oset->shift(); $this->assertEquals("short_description", $case->name); $this->assertEquals("not", $case->sortable,"With name: short_description");$this->assertEquals("1", $case->importancy,"With name: short_description");$this->assertEquals("description", $case->r_display_identifier,"With name: short_description");$this->assertEquals("", $case->facet_type,"With name: short_description");
$case = $oset->shift(); $this->assertEquals("sku", $case->name); $this->assertEquals("not", $case->sortable,"With name: sku");$this->assertEquals("3", $case->importancy,"With name: sku");$this->assertEquals("sku", $case->r_display_identifier,"With name: sku");$this->assertEquals("", $case->facet_type,"With name: sku");
$case = $oset->shift(); $this->assertEquals("status", $case->name); $this->assertEquals("not", $case->sortable,"With name: status");$this->assertEquals("0", $case->importancy,"With name: status");$this->assertEquals("", $case->r_display_identifier,"With name: status");$this->assertEquals("", $case->facet_type,"With name: status");
$case = $oset->shift(); $this->assertEquals("tax_class_id", $case->name); $this->assertEquals("not", $case->sortable,"With name: tax_class_id");$this->assertEquals("0", $case->importancy,"With name: tax_class_id");$this->assertEquals("", $case->r_display_identifier,"With name: tax_class_id");$this->assertEquals("", $case->facet_type,"With name: tax_class_id");
$case = $oset->shift(); $this->assertEquals("type", $case->name); $this->assertEquals("not", $case->sortable,"With name: type");$this->assertEquals("0", $case->importancy,"With name: type");$this->assertEquals("", $case->r_display_identifier,"With name: type");$this->assertEquals("", $case->facet_type,"With name: type");
$case = $oset->shift(); $this->assertEquals("type_id", $case->name); $this->assertEquals("not", $case->sortable,"With name: type_id");$this->assertEquals("0", $case->importancy,"With name: type_id");$this->assertEquals("", $case->r_display_identifier,"With name: type_id");$this->assertEquals("", $case->facet_type,"With name: type_id");
$case = $oset->shift(); $this->assertEquals("updated_at", $case->name); $this->assertEquals("not", $case->sortable,"With name: updated_at");$this->assertEquals("0", $case->importancy,"With name: updated_at");$this->assertEquals("", $case->r_display_identifier,"With name: updated_at");$this->assertEquals("", $case->facet_type,"With name: updated_at");
$case = $oset->shift(); $this->assertEquals("url", $case->name); $this->assertEquals("not", $case->sortable,"With name: url");$this->assertEquals("0", $case->importancy,"With name: url");$this->assertEquals("link", $case->r_display_identifier,"With name: url");$this->assertEquals("", $case->facet_type,"With name: url");
$case = $oset->shift(); $this->assertEquals("url_key", $case->name); $this->assertEquals("not", $case->sortable,"With name: url_key");$this->assertEquals("0", $case->importancy,"With name: url_key");$this->assertEquals("", $case->r_display_identifier,"With name: url_key");$this->assertEquals("", $case->facet_type,"With name: url_key");
$case = $oset->shift(); $this->assertEquals("visibility", $case->name); $this->assertEquals("not", $case->sortable,"With name: visibility");$this->assertEquals("0", $case->importancy,"With name: visibility");$this->assertEquals("", $case->r_display_identifier,"With name: visibility");$this->assertEquals("", $case->facet_type,"With name: visibility");
$case = $oset->shift(); $this->assertEquals("weight", $case->name); $this->assertEquals("not", $case->sortable,"With name: weight");$this->assertEquals("0", $case->importancy,"With name: weight");$this->assertEquals("", $case->r_display_identifier,"With name: weight");$this->assertEquals("", $case->facet_type,"With name: weight");
}   
   
function testAutowebshop12(){
    
     $wid = 1;
      //  define('WID',1);
      $db = vpdo::getVdo(DB_PREFIX.$wid);
      $db->exec("truncate table options_settings");
        ScrapeMode::set('full');
      $scrape = new TestScraper(1);
      $scrape->prepare();
    $db->exec("insert into options_tmp (name) values (".$db->quote("buy_link").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("category").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("category_id").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("lowest_price").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("pid").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("products_description").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("products_image").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("products_model").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("products_name").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("products_price").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("products_quantity").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("sku").")");
$db->exec("insert into options_tmp (name) values (".$db->quote("url").")");
$case = OptionHandler::createOptionsSetting("buy_link",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("category",4); $case->save($wid);
$case = OptionHandler::createOptionsSetting("category_id",4); $case->save($wid);
$case = OptionHandler::createOptionsSetting("lowest_price",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("pid",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("products_description",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("products_image",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("products_model",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("products_name",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("products_price",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("products_quantity",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("sku",1); $case->save($wid);
$case = OptionHandler::createOptionsSetting("url",1); $case->save($wid);
OptionHandler::correctOptionsSettings($wid);
$oset = new OptionsSettingSet($wid); $oset->fillFromDb( "order by name asc");
$case = $oset->shift(); $this->assertEquals("buy_link", $case->name); $this->assertEquals("not", $case->sortable,"With name: buy_link");$this->assertEquals("0", $case->importancy,"With name: buy_link");$this->assertEquals("buy_link", $case->r_display_identifier,"With name: buy_link");$this->assertEquals("", $case->facet_type,"With name: buy_link");
$case = $oset->shift(); $this->assertEquals("category", $case->name); $this->assertEquals("not", $case->sortable,"With name: category");$this->assertEquals("2", $case->importancy,"With name: category");$this->assertEquals("", $case->r_display_identifier,"With name: category");$this->assertEquals("", $case->facet_type,"With name: category");
$case = $oset->shift(); $this->assertEquals("category_id", $case->name); $this->assertEquals("not", $case->sortable,"With name: category_id");$this->assertEquals("0", $case->importancy,"With name: category_id");$this->assertEquals("", $case->r_display_identifier,"With name: category_id");$this->assertEquals("", $case->facet_type,"With name: category_id");
$case = $oset->shift(); $this->assertEquals("lowest_price", $case->name); $this->assertEquals("number", $case->sortable,"With name: lowest_price");$this->assertEquals("0", $case->importancy,"With name: lowest_price");$this->assertEquals("lowest_price", $case->r_display_identifier,"With name: lowest_price");$this->assertEquals("", $case->facet_type,"With name: lowest_price");
$case = $oset->shift(); $this->assertEquals("pid", $case->name); $this->assertEquals("not", $case->sortable,"With name: pid");$this->assertEquals("0", $case->importancy,"With name: pid");$this->assertEquals("pid", $case->r_display_identifier,"With name: pid");$this->assertEquals("", $case->facet_type,"With name: pid");
$case = $oset->shift(); $this->assertEquals("products_description", $case->name); $this->assertEquals("not", $case->sortable,"With name: products_description");$this->assertEquals("1", $case->importancy,"With name: products_description");$this->assertEquals("description", $case->r_display_identifier,"With name: products_description");$this->assertEquals("", $case->facet_type,"With name: products_description");
$case = $oset->shift(); $this->assertEquals("products_image", $case->name); $this->assertEquals("not", $case->sortable,"With name: products_image");$this->assertEquals("0", $case->importancy,"With name: products_image");$this->assertEquals("image_link", $case->r_display_identifier,"With name: products_image");$this->assertEquals("", $case->facet_type,"With name: products_image");
$case = $oset->shift(); $this->assertEquals("products_model", $case->name); $this->assertEquals("not", $case->sortable,"With name: products_model");$this->assertEquals("3", $case->importancy,"With name: products_model");$this->assertEquals("", $case->r_display_identifier,"With name: products_model");$this->assertEquals("", $case->facet_type,"With name: products_model");
$case = $oset->shift(); $this->assertEquals("products_name", $case->name); $this->assertEquals("string", $case->sortable,"With name: products_name");$this->assertEquals("3", $case->importancy,"With name: products_name");$this->assertEquals("name", $case->r_display_identifier,"With name: products_name");$this->assertEquals("", $case->facet_type,"With name: products_name");
$case = $oset->shift(); $this->assertEquals("products_price", $case->name); $this->assertEquals("not", $case->sortable,"With name: products_price");$this->assertEquals("0", $case->importancy,"With name: products_price");$this->assertEquals("price", $case->r_display_identifier,"With name: products_price");$this->assertEquals("", $case->facet_type,"With name: products_price");
$case = $oset->shift(); $this->assertEquals("products_quantity", $case->name); $this->assertEquals("not", $case->sortable,"With name: products_quantity");$this->assertEquals("0", $case->importancy,"With name: products_quantity");$this->assertEquals("quantity", $case->r_display_identifier,"With name: products_quantity");$this->assertEquals("", $case->facet_type,"With name: products_quantity");
$case = $oset->shift(); $this->assertEquals("sku", $case->name); $this->assertEquals("not", $case->sortable,"With name: sku");$this->assertEquals("3", $case->importancy,"With name: sku");$this->assertEquals("sku", $case->r_display_identifier,"With name: sku");$this->assertEquals("", $case->facet_type,"With name: sku");
$case = $oset->shift(); $this->assertEquals("url", $case->name); $this->assertEquals("not", $case->sortable,"With name: url");$this->assertEquals("0", $case->importancy,"With name: url");$this->assertEquals("link", $case->r_display_identifier,"With name: url");$this->assertEquals("", $case->facet_type,"With name: url");
}   
   
   
    
}


