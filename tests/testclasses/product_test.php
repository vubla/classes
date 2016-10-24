<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';


$suite  = new PHPUnit_Framework_TestSuite("ProductTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);




class FakeVdo{
    public $value_set = array();
    
    function fetchOne(){
       // var_dump($this->value_set);
        return array_shift($this->value_set);
    }
}



class TestProduct extends Product{
    
    public $result;
    
    function __construct(){
        $this->wid = 1;
        $this->vdo = New FakeVdo();
    }
    
    function saveOptions($i){
        $this->result = $i;
    }
}
if(!class_exists("TestScraper"))
{
    class TestScraper extends Scraper{
        function getFetcher(){
          
        }
    }
}

class ProductTest extends BaseDbTest 
{
    public $data;
    
    private $test_max_prod;
    private $initial;
    
    function setUp() {
        $this->buildDatabases();
        $array = json_decode('{"categories":["4"],"pid":"1","wid":1,"options":[{"name":"products_model","value":{"name":"MG200MMS"}},{"name":"products_image","value":{"name":"images\/matrox\/mg200mms.gif"}},{"name":"products_price","value":{"name":"299.9900"}},{"name":"manufacturers_name","value":{"name":"Matrox"}},{"name":"manufacturers_id","value":{"name":"1"}},{"name":"url","value":{"name":"product_info.php?products_id=1"}},{"name":"products_name","value":{"name":"Matrox G200 MMS"}},{"name":"products_description","value":{"name":"Reinforcing its position as a multi-monitor trailblazer, Matrox Graphics Inc. has once again developed the most flexible and highly advanced solution in the industry. Introducing the new Matrox G200 Multi-Monitor Series; the first graphics card ever to support up to four DVI digital flat panel displays on a single 8\" PCI board.With continuing demand for digital flat panels in the financial workplace, the Matrox G200 MMS is the ultimate in flexible solutions. The Matrox G200 MMS also supports the new digital video interface (DVI) created by the Digital Display Working Group (DDWG) designed to ease the adoption of digital flat panels. Other configurations include composite video capture ability and onboard TV tuner, making the Matrox G200 MMS the complete solution for business needs.Based on the award-winning MGA-G200 graphics chip, the Matrox G200 Multi-Monitor Series provides superior 2D\/3D graphics acceleration to meet the demanding needs of business applications such as real-time stock quotes (Versus), live video feeds (Reuters & Bloombergs), multiple windows applications, word processing, spreadsheets and CAD."}},{"name":"buy_link","value":{"name":"index.php?cPath=3_10&sort=2a&action=buy_now&products_id=1"}},{"name":"Model","value":[{"products_attributes_id":"4","products_id":"1","options_id":"3","options_values_id":"5","value_price":"0.0000","price_prefix":"+","products_options_values_id":"5","language_id":"1","name":"Value"},{"products_attributes_id":"5","products_id":"1","options_id":"3","options_values_id":"6","value_price":"100.0000","price_prefix":"+","products_options_values_id":"6","language_id":"1","name":"Premium"}]},{"name":"Memory","value":[{"products_attributes_id":"1","products_id":"1","options_id":"4","options_values_id":"1","value_price":"0.0000","price_prefix":"+","products_options_values_id":"1","language_id":"1","name":"4 mb"},{"products_attributes_id":"2","products_id":"1","options_id":"4","options_values_id":"2","value_price":"50.0000","price_prefix":"+","products_options_values_id":"2","language_id":"1","name":"8 mb"},{"products_attributes_id":"3","products_id":"1","options_id":"4","options_values_id":"3","value_price":"70.0000","price_prefix":"+","products_options_values_id":"3","language_id":"1","name":"16 mb"}]},{"name":"pid","value":{"name":"1"}}]}',true);
        $className = 'Product';
        $this->data = unserialize(sprintf(
        'O:%d:"%s"%s',
        strlen($className),
        $className,
        strstr(serialize($array), ':')
        ));
        
        $vdo = VPDO::getVdo(DB_METADATA);
        @$vdo->exec('drop database phpunit___temp');
        //new ProductFinder(1);
        ScrapeMode::set('full');
        $this->scraper = new TestScraper(1);
        $this->scraper->prepare();
    }
    
    function tearDown() {
       
        $this->dropDatabases();
    }
    
   
    function testSplitWords()
    {
        $this->assertEquals(true, true);
        $input = "Slank-O-Fort";
        $expected = array("Fort","O","Slank" , "SlankOFort", "Slank-O-Fort", "Slank-O", "O-Fort");
       sort($expected);
        
        $result = product::splitWords($input);
        sort($result);
        $this->assertEquals($expected, $result);
        
    }
    function testSplitWordsScraper()
    {
        $this->assertEquals(true, true);
        $input = "Bord- og vægskraber";
        $expected = array("Bord","og","vægskraber");
       sort($expected);
        
        $result = product::splitWords($input);
        sort($result);
        $this->assertEquals($expected, $result);
        
    }
    
    function testSplitWordsStart()
    {
        $this->assertEquals(true, true);
        $input = "-Fort";
        $expected = array("Fort");
        sort($expected);
        
        $result = product::splitWords($input);
        sort($result);
        $this->assertEquals($expected, $result);
        
    }
    
    function testSaveCategories() {
        $c1 = new Category(1);
        $c1->parent_id = 2;
        $c1->cid = 4;
        $c1->name = 'Johnson';
        $c2 = new Category(1);
        $c2->parent_id = 0;
        $c2->cid = 2;
        $c2->name = 'John';
        Category::$cats[4] = $c1;
        Category::$cats[2] = $c2;
        $this->data->saveCategories(1);
        $this->assertEquals('Johnson',$this->data->options['category']['value'][0]['name']);
        $this->assertEquals('John',$this->data->options['category']['value'][1]['name']);
        $this->assertEquals('4',$this->data->options['category_id']['value'][0]['name']);
        $this->assertEquals('2',$this->data->options['category_id']['value'][1]['name']);
        

    }
    
    function testSaveOptions(){
       $this->data->saveOptions(1);
       
       $vdo = VPDO::getVdo(DB_PREFIX.'1');
       $options = $vdo->fetchOne('select count(*) from options_tmp');
      // $products = $vdo->fetchOne('select count(*) from products');
       $options_values = $vdo->fetchOne('select count(*) from options_values_tmp');
       
       $this->assertEquals(12,$options);
      // $this->assertEquals(28,$products);
       $this->assertEquals(15,$options_values);
    }
    
    function testisSavableOption(){
        $this->data->_isSavableOption('discount_price', 1);
        
         $vdo = VPDO::getVdo(DB_PREFIX.'1');
         $res = $vdo->fetchOne('select count(*) from options_settings where name = ?', array('discount_price'));
         $this->assertEquals(1, $res);
    }
    
    function testFindAndSafeLowestPrice(){
        $vdo = VPDO::getVdo(DB_PREFIX.'1');
       $this->assertCount(12,$this->data->options);
       $this->data->options['discount_price'] = array('name'=>'discount_price', 'value'=>array('name'=>12));
       $this->assertCount(13,$this->data->options);
       $this->data->saveOptions(1);
   
       $options = $vdo->fetchOne('select count(*) from options_tmp');
      // $products = $vdo->fetchOne('select count(*) from products_tmp');
       $options_values = $vdo->fetchOne('select count(*) from options_values_tmp');
       
       $this->assertEquals(13,$options);
       //$this->assertEquals(28,$products);
       $this->assertEquals(16,$options_values);
       
       
       $this->data->findAndSaveLowestPrice(1);
       $options = $vdo->fetchOne('select count(*) from options_tmp');
  
       $options_values = $vdo->fetchOne('select count(*) from options_values_tmp');
       
       $this->assertEquals(14,$options);
  
       $this->assertEquals(17,$options_values);
   
       $res = $vdo->fetchOne("select ov.name from options_settings os inner join options_tmp o on o.name = os.name  inner join options_values_tmp ov on o.id = ov.option_id  where r_display_identifier = 'lowest_price' and product_id = 1");
      
       $this->assertEquals(12, $res);
    }
    
    function testSave()
    {
       ScrapeMode::set('full');
       $vdo = VPDO::getVdo(DB_PREFIX.'1'); 
       $this->data->save();
       $options = $vdo->fetchOne('select count(*) from options_tmp');
       $options_values = $vdo->fetchOne('select count(*) from options_values_tmp'); 
       $this->assertEquals(15,$options);    
       $this->assertEquals(20,$options_values);

    }

    function testDelete()
    {
         $vdo = VPDO::getVdo(DB_PREFIX.'1'); 
         $producttables = array( 'options_values' , 'word_relation');
         
         // First part verifies that it is saved correctly
         $this->testSave();
         foreach($producttables as $pt){
             $c = $vdo->fetchOne('select count(*) from '.$pt.' where product_id = 1');
             $this->assertGreaterThan(0,$c);
         }
         $c = $vdo->fetchOne('select count(*) from products where id = 1');
         $this->assertEquals(1, $c);
         
         /// This part verifies that is got deleted.
         $this->data->delete();
         foreach($producttables as $pt){
             $c = $vdo->fetchOne('select count(*) from '.$pt.' where product_id = 1');
             $this->assertEquals(0, 0);
         }
         $c = $vdo->fetchOne('select count(*) from products where id = 1');
         $this->assertEquals( 0, $c);
         
    }

    function testSaveUpdateMode()
    {
       ScrapeMode::set('update');
       
       $vdo = VPDO::getVdo(DB_PREFIX.'1'); 
       $this->data->save();
       $product_id = $vdo->fetchOne('select id from products where pid = 1');
       $options = $vdo->fetchOne('select count(*) from options');
       $options_values = $vdo->fetchOne('select count(*) from options_values where product_id = '.$product_id); 
      
       $this->assertGreaterThan(0,$product_id);
       $this->assertEquals(17,$options);   

       $this->assertEquals(20,$options_values);
       
        ScrapeMode::set('full');
     
        
    }




    
    function testFindAndSafeLowestPrice2(){
        $vdo = VPDO::getVdo(DB_PREFIX.'1');
        $this->data->saveOptions(1);
   
       $options = $vdo->fetchOne('select count(*) from options_tmp');
       $options_values = $vdo->fetchOne('select count(*) from options_values_tmp');  
       $this->assertEquals(12,$options);
       $this->assertEquals(15,$options_values);
       $this->data->findAndSaveLowestPrice(1);
 
       $options = $vdo->fetchOne('select count(*) from options_tmp');
       $options_values = $vdo->fetchOne('select count(*) from options_values_tmp');  
       $this->assertEquals(13,$options);
       $this->assertEquals(16,$options_values);
      
      
       $res = $vdo->fetchOne("select ov.name from options_settings os inner join options_tmp o on o.name = os.name  inner join options_values_tmp ov on o.id = ov.option_id  where r_display_identifier = 'lowest_price' and product_id = 1");
       $this->assertEquals('299.9900', $res);
    }
    
    function testFindAndSafeLowestPrice3(){
        $vdo = VPDO::getVdo(DB_PREFIX.'1');
        //$this->data->saveOptions(1);
        $array = json_decode('{"categories":["4"],"pid":"2","wid":1,"options":[{"name":"products_model","value":{"name":"MG200MMS"}},{"name":"discount_price","value":{"name":"100"}},{"name":"products_image","value":{"name":"images\/matrox\/mg200mms.gif"}},{"name":"products_price","value":{"name":"123"}},{"name":"manufacturers_name","value":{"name":"Matrox"}},{"name":"manufacturers_id","value":{"name":"1"}},{"name":"url","value":{"name":"product_info.php?products_id=2"}},{"name":"products_name","value":{"name":"Matrox G200 MMS"}},{"name":"products_description","value":{"name":"Reinforcing its position as a multi-monitor trailblazer, Matrox Graphics Inc. has once again developed the most flexible and highly advanced solution in the industry. Introducing the new Matrox G200 Multi-Monitor Series; the first graphics card ever to support up to four DVI digital flat panel displays on a single 8\" PCI board.With continuing demand for digital flat panels in the financial workplace, the Matrox G200 MMS is the ultimate in flexible solutions. The Matrox G200 MMS also supports the new digital video interface (DVI) created by the Digital Display Working Group (DDWG) designed to ease the adoption of digital flat panels. Other configurations include composite video capture ability and onboard TV tuner, making the Matrox G200 MMS the complete solution for business needs.Based on the award-winning MGA-G200 graphics chip, the Matrox G200 Multi-Monitor Series provides superior 2D\/3D graphics acceleration to meet the demanding needs of business applications such as real-time stock quotes (Versus), live video feeds (Reuters & Bloombergs), multiple windows applications, word processing, spreadsheets and CAD."}},{"name":"buy_link","value":{"name":"index.php?cPath=3_10&sort=2a&action=buy_now&products_id=2"}},{"name":"Model","value":[{"products_attributes_id":"4","products_id":"2","options_id":"3","options_values_id":"5","value_price":"0.0000","price_prefix":"+","products_options_values_id":"5","language_id":"1","name":"Value"},{"products_attributes_id":"5","products_id":"2","options_id":"3","options_values_id":"6","value_price":"100.0000","price_prefix":"+","products_options_values_id":"6","language_id":"1","name":"Premium"}]},{"name":"Memory","value":[{"products_attributes_id":"1","products_id":"2","options_id":"4","options_values_id":"1","value_price":"0.0000","price_prefix":"+","products_options_values_id":"1","language_id":"1","name":"4 mb"},{"products_attributes_id":"2","products_id":"2","options_id":"4","options_values_id":"2","value_price":"50.0000","price_prefix":"+","products_options_values_id":"2","language_id":"1","name":"8 mb"},{"products_attributes_id":"3","products_id":"2","options_id":"4","options_values_id":"3","value_price":"70.0000","price_prefix":"+","products_options_values_id":"3","language_id":"1","name":"16 mb"}]},{"name":"pid","value":{"name":"2"}}]}',true);
        $className = 'Product';
        $data = unserialize(sprintf(
        'O:%d:"%s"%s',
        strlen($className),
        $className,
        strstr(serialize($array), ':')
        ));
        $data->saveOptions(2);
       $options = $vdo->fetchOne('select count(*) from options_tmp');
       $options_values = $vdo->fetchOne('select count(*) from options_values_tmp');  
       $this->assertEquals(13,$options);
       $this->assertEquals(16,$options_values);
       $this->data->findAndSaveLowestPrice(2);
 
       $options = $vdo->fetchOne('select count(*) from options_tmp');
       $options_values = $vdo->fetchOne('select count(*) from options_values_tmp');  
       $this->assertEquals(14,$options);
       $this->assertEquals(17,$options_values);     
      
       $res = $vdo->fetchOne("select ov.name 
                              from 
                                options_settings os inner join options_tmp o 
                                    on o.name = os.name 
                                inner join options_values_tmp ov 
                                    on o.id = ov.option_id  
                              where r_display_identifier = 'lowest_price' and product_id = 2");
       $this->assertEquals('100', $res);
    }

    function testgetPropductProperty(){
        
        $result = product::getProductProperty('buy_link',1,1);
        $expected = 'index.php?cPath=3_10&sort=2a&action=buy_now&products_id=1';
        $this->assertEquals($expected,$result );
    }
    
    function testfindAndSaveLowestPrice(){
            
        $subject = new TestProduct();    
        
        $subject->vdo->value_set = array(10, 10);
        $subject->findAndSaveLowestPrice(1);
        $this->assertEquals(10, $subject->options['value']['name']);    
        
        $subject->vdo->value_set = array(null, 10);
        $subject->findAndSaveLowestPrice(1);
        $this->assertEquals(10, $subject->options['value']['name']);
        
        $subject->vdo->value_set = array(10, null);
        $subject->findAndSaveLowestPrice(1);
        $this->assertEquals(10, $subject->options['value']['name']);
        
        $subject->vdo->value_set = array(10, 11);
        $subject->findAndSaveLowestPrice(1);
        $this->assertEquals(10, $subject->options['value']['name']);
        
               
        $subject->vdo->value_set = array(11, 10);
        $subject->findAndSaveLowestPrice(1);
        $this->assertEquals(10, $subject->options['value']['name']);
        
        
        $subject->vdo->value_set = array(0, 11);
        $subject->findAndSaveLowestPrice(1);
        $this->assertEquals(0, $subject->options['value']['name']);
        
        $subject->vdo->value_set = array(11, 0);
        $subject->findAndSaveLowestPrice(1);
        $this->assertEquals(0, $subject->options['value']['name']);
        
        
        $subject->vdo->value_set = array(0, 0);
        $subject->findAndSaveLowestPrice(1);
        $this->assertEquals(0, $subject->options['value']['name']);
        
        $subject->vdo->value_set = array(0, null);
        $subject->findAndSaveLowestPrice(1);
        $this->assertEquals(0, $subject->options['value']['name']);
        
        $subject->vdo->value_set = array(10.1, 10);
        $subject->findAndSaveLowestPrice(1);
        $this->assertEquals(10, $subject->options['value']['name']);
        
        $subject->vdo->value_set = array(null, null);
        $subject->findAndSaveLowestPrice(1);
        $this->assertEquals(null, $subject->options['value']['name']);
        
        $subject->vdo->value_set = array("0", 10);
        $subject->findAndSaveLowestPrice(1);
        $this->assertEquals(0, $subject->options['value']['name']);
        
        $subject->vdo->value_set = array(10, "0");
        $subject->findAndSaveLowestPrice(1);
        $this->assertEquals(0, $subject->options['value']['name']);
        
        $subject->vdo->value_set = array(10, "0.000");
        $subject->findAndSaveLowestPrice(1);
        $this->assertEquals(0, $subject->options['value']['name']);
        
        $subject->vdo->value_set = array("0.000", 10);
        $subject->findAndSaveLowestPrice(1);
        $this->assertEquals(0, $subject->options['value']['name']);
        
        $subject->vdo->value_set = array(10, 0.000);
        $subject->findAndSaveLowestPrice(1);
        $this->assertEquals(0, $subject->options['value']['name']);
        
        $subject->vdo->value_set = array(0.000, 10);
        $subject->findAndSaveLowestPrice(1);
        $this->assertEquals(0, $subject->options['value']['name']);
    }
   
}






