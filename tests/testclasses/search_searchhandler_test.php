<?php


require_once '../vublamailer.php'; // Not the real one
require_once '../basedbtest.php';


$suite  = new PHPUnit_Framework_TestSuite("SearchHandlerTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);


class SearchHandlerTestObject  extends SearchHandler
{
    function __construct(){
        parent::__construct(1);
        $this->searcher = new SearcherTestObject(1);
        $this->meta = VPDO::getVdo(DB_METADATA);
      
        $this->wpdo = VPDO::getVdo(DB_PREFIX . 1);
    }
}

class SearcherTestObject extends Searcher
{
    var $min_opt;
    var $max_opt;
    var $eq_opt;
    
    function __construct(){
        $ref = "";
        parent::__construct(1,$ref);
    }
    
    function setMinOptions($e){
        $this->min_opt = $e;
    }
    function setMaxOptions($e){
        $this->max_opt = $e;
    }
    function setEqOptions($e){
        $this->eq_opt = $e;
    }
    function setSortBy($e){
        $this->sort_by = $e;
    }
    function setSortOrder($e){
        $this->sortorder = $e;
    }
  
}


class SearchHandlerTest extends BaseDbTest 
{
    var $wid = 1;
    function setUp() 
    {
      
        $this->buildDatabases(); 
       
        $_GET = array();
         settings::setGlobal('api_out', 0);

    }

    function tearDown() 
    {
        unset($this->filter);
        $this->dropDatabases();
        $_GET = array();
    }
    
    function testConstructer()
    {
        $_GET['host'] = 'everlight.dk';
        $_GET['q'] = 'dvd';
        $h = new SearchHandler(1);
        
        $this->assertInstanceOf('vpdo',$h->meta);
        
        
    }
    
    
    
    function testinputEncode()
    {
        
        
    }
    
    function testfixCatsAndKillParents(){
        $_GET['host'] = 'everlight.dk';
        $_GET['q'] = 'dvd';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 1;
        $_GET['postvar'] = json_encode(array('name'=>1,3,4,5));
        $_GET['getvar'] = json_encode(array('name'=>1,3,4,5));
        Settings::setLocal('enabled',1,1);
        $h = new SearchHandler();
        // All get and post data i s ignored
        
        
        $data = array('Bil', 'Xenon Pærer@Bil');
        $expected = array('Xenon Pærer');
        $result = $h->fixCatsAndKillParents($data);
        $this->assertCount(1,$result);
        $this->assertEquals('Xenon Pærer',$result[0]);
        
        
        $data = array('Bil', 'Xenon Pærer@Bil', 'supereman@qwerty', 'qwerty', 'johnson@ggffg');
        $result = $h->fixCatsAndKillParents($data);
        $this->assertCount(3,$result);
        $this->assertEquals('Xenon Pærer',$result[0]);
        $this->assertEquals('supereman',$result[1]);
        $this->assertEquals('johnson',$result[2]);
        
        
          $data = array('Bi l', 'Xenon Pærer@Bi l', 'supEre man@qwerty', 'qwerty', 'johnson@ggffg');
        $result = $h->fixCatsAndKillParents($data);
        $this->assertCount(3,$result);
        $this->assertEquals('Xenon Pærer',$result[0]);
        $this->assertEquals('supEre man',$result[1]);
        $this->assertEquals('johnson',$result[2]);
    }
    
    function testinitialize()
    {
        $_GET['host'] = 'everlight.dk';
        $_GET['q'] = 'dvd';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 1;
        $_GET['postvar'] = json_encode(array('name'=>1,3,4,5));
        $_GET['getvar'] = json_encode(array('name'=>1,3,4,5));
        Settings::setLocal('enabled',1,1);
        $h = new SearchHandler();
      //    var_dump($_GET);
       // $h->initialize();
        
      
     
        $this->assertEquals(1,$h->wid );
     
        $this->assertEquals(1, $this->wid);
        $this->assertEquals('somefile.php', $h->shopFile);
     //   var_dump($_GET);  
        $this->assertInstanceOf('stdclass', $_GET['postvar']);
        $this->assertInstanceOf('stdclass', $_GET['getvar']);
        $this->assertEquals(false, $h->logthis);
        
    }
    
     function testinitialize2()
    {
        $_GET['host'] = 'everlight.dk';
        $_GET['q'] = 'dvd';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 0;
        $_GET['postvar'] = json_encode(array('name'=>1,3,4,5));
        $_GET['getvar'] = json_encode(array('name'=>1,3,4,5));
      //  Settings::setLocal('enabled',1,$this->wid);
        $h = new SearchHandler(); 
       // $h->initialize();
        
        
        $this->assertEquals(1,$h->wid );
      

        $this->assertEquals('somefile.php', $h->shopFile);
       
        $this->assertInstanceOf('stdclass', $_GET['postvar']);
        $this->assertInstanceOf('stdclass', $_GET['getvar']);
        $this->assertEquals(true, $h->logthis);
        
    }
    
    
    function testhandleOptions()
    {
        $_GET['host'] = 'everlight.dk';
        $_GET['q'] = 'dvd';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 1;
        $_GET['min_price'] = 12;
        $_GET['postvar'] = json_encode(array('name'=>1,'hohn'=>3,4,5));
        $_GET['getvar'] = json_encode(array('name'=>1,'nameff'=>3,4,5));
        Settings::setLocal('enabled',1,1);
        $h = new SearchHandlerTestObject();
        $h->handleOptions();
        
        $this->assertInternalType('array',$h->searcher->min_opt);
        $this->assertInternalType('array',$h->searcher->max_opt);
        $this->assertInternalType('array',$h->searcher->eq_opt);
       
        $this->assertCount(1, $h->searcher->min_opt);
        $this->assertEquals(array('lowest_price'=>12), $h->searcher->min_opt);
        $this->assertCount(0,$h->searcher->max_opt);
        $this->assertCount(0,$h->searcher->eq_opt);
    }
    
     function testhandleOptions2()
    {
        unset($_POST);
        $_GET  = array();
        $_GET['host'] = 'everlight.dk';
        $_GET['q'] = 'dvd';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 1;
        
        $_GET['max_price'] = 120;
        $_GET['postvar'] = json_encode(array('min_price'=>12,'hohn'=>3,4,5));
        $_GET['getvar'] = json_encode(array('name'=>1,'nameff'=>3,4,5));
        Settings::setLocal('enabled',1,1);
        $h = new SearchHandlerTestObject();
        $h->handleOptions();
        
        $this->assertInternalType('array',$h->searcher->min_opt);
        $this->assertInternalType('array',$h->searcher->max_opt);
        $this->assertInternalType('array',$h->searcher->eq_opt);
       
        $this->assertCount(1, $h->searcher->min_opt);
        $this->assertEquals(array('lowest_price'=>12), $h->searcher->min_opt);
        $this->assertCount(1,$h->searcher->max_opt);
        $this->assertEquals(array('lowest_price'=>120), $h->searcher->max_opt);
        $this->assertCount(0,$h->searcher->eq_opt);
        
    }


    function testhandleOptions3()
    {
        $_GET['host'] = 'everlight.dk';
        $_GET['q'] = 'dvd';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 1;
     
        $_GET['max_price'] = 120;
        $_GET['postvar'] = json_encode(array('min_price'=>12,'hohn'=>3,4,5));
        $_GET['getvar'] = json_encode(array('eq_options'=>array('category'=>array(1,2,3,4)),'nameff'=>3,4,5));
        Settings::setLocal('enabled',1,1);
        $h = new SearchHandlerTestObject();
        $h->handleOptions();
        
        $this->assertInternalType('array',$h->searcher->min_opt);
        $this->assertInternalType('array',$h->searcher->max_opt);
        $this->assertInternalType('array',$h->searcher->eq_opt);
       
        $this->assertCount(1, $h->searcher->min_opt);
        $this->assertEquals(array('lowest_price'=>12), $h->searcher->min_opt);
        $this->assertCount(1,$h->searcher->max_opt);
        $this->assertEquals(array('lowest_price'=>120), $h->searcher->max_opt);
        $this->assertCount(1,$h->searcher->eq_opt);
        // The EQ is not thorougly tested, it must be investigated how the categories are encoded. 
    }

    function testhandleSortby()
    {
        $_GET['host'] = 'everlight.dk';
        $_GET['q'] = 'dvd';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 1;
     
        $_GET['sort_by'] = 'name';
        $_GET['sortorder'] = 'asc';
        Settings::setLocal('enabled',1,1);
        $h = new SearchHandlerTestObject();
        $h->handleSortBy();
        $this->assertEquals('asc', $h->searcher->sortorder);
        $this->assertEquals('name', $h->searcher->sort_by);

     
     
        $_GET['sort_by'] = 'name@asc';
        $_GET['sortorder'] = '';
        Settings::setLocal('enabled',1,1);
        $h = new SearchHandlerTestObject();
        $h->handleSortBy();
        $this->assertEquals('asc', $h->searcher->sortorder);
        $this->assertEquals('name', $h->searcher->sort_by);
  
  
  
        $_GET['sort_by'] = 'name@asc';
        $_GET['sortorder'] = 'desc';
        Settings::setLocal('enabled',1,1);
        $h = new SearchHandlerTestObject();
        $h->handleSortBy();
        $this->assertEquals('desc', $h->searcher->sortorder);
        $this->assertEquals('name', $h->searcher->sort_by);
    }
    

    function testResults()
    {
        $_GET['host'] = 'everlight.dk';
        $_GET['q'] = 'dvd';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 1;
     
        $_GET['max_price'] = 120;
        $_GET['postvar'] = json_encode(array('min_price'=>12,'hohn'=>3,4,5));
        $_GET['getvar'] = json_encode(array('eq_options'=>array('category'=>array(1,2,3,4)),'nameff'=>3,4,5));
        Settings::setLocal('enabled',1,1);
        $h = new SearchHandler();
        $res = $h->getOutput();
        
        $this->assertInternalType('string',$res);
    
        // This should be more tested.
        // Template should also be more tested. 
        // Infact the result should be more or less tested in the templates and the searcher.
    }

    function testResultsJSon()
    {
        $_GET['host'] = 'everlight.dk';
        $_GET['q'] = 'dvd';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 1;
     
      //  $_GET['max_price'] = 120;
      //  $_GET['postvar'] = json_encode(array('min_price'=>12,'hohn'=>3,4,5));
      //  $_GET['getvar'] = json_encode(array('eq_options'=>array('category'=>array(1,2,3,4)),'nameff'=>3,4,5));
        Settings::setLocal('search_result_output_format','ids',$this->wid);
        $h = new SearchHandler();
        $res = $h->getOutput();
       // $this->assertEquals('dvd',$h->searcher->stringFilter->original);
        
        $this->assertInternalType('string',$res);
         
        $res = json_decode($res);
        $this->assertInstanceOf('stdclass', $res);

        $this->assertCount(18,(array) $res->ids);
        foreach ($res->ids as $key => $value) {
            $this->assertInternalType('int', (int)$value);
        }
        // This should be more tested.
        // Template should also be more tested. 
        // Infact the result should be more or less tested in the templates and the searcher.
    }


    function testResultsJSonWithFilter()
    {
        $_GET['host'] = 'everlight.dk';
        $_GET['q'] = 'dvd';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 1;
     
      //  $_GET['max_price'] = 120;
        $_GET['postvar'] = json_encode(array('min_price'=>300,'hohn'=>3,4,5));
      //  $_GET['getvar'] = json_encode(array('eq_options'=>array('category'=>array(1,2,3,4)),'nameff'=>3,4,5));
        Settings::setLocal('search_result_output_format','ids',$this->wid);
        $h = new SearchHandler();
        $res = $h->getOutput();
       // $this->assertEquals('dvd',$h->searcher->original);
        
        $this->assertInternalType('string',$res);
         
        $res = json_decode($res);
        $this->assertInstanceOf('stdclass', $res);
        $this->assertCount(1, $res->ids);
        foreach ($res->ids as $key => $value) {
            $this->assertInternalType('int', (int)$value);
        }
        
        $_GET['min_price'] = 300;
        //  $_GET['max_price'] = 120;
        $_GET['postvar'] = '';
      //  $_GET['getvar'] = json_encode(array('eq_options'=>array('category'=>array(1,2,3,4)),'nameff'=>3,4,5));
        Settings::setLocal('search_result_output_format','ids',1);
        $h = new SearchHandler();
        $res = $h->getOutput();
        //
        
        $this->assertInternalType('string',$res);
         
        $res = json_decode($res);
        $this->assertInstanceOf('stdclass', $res);
        $this->assertCount(1, $res->ids);
        foreach ($res->ids as $key => $value) {
            $this->assertInternalType('int', (int)$value);
        }
    }




    function testResultsHTML()
    {
       
        $_GET['host'] = 'everlight.dk';
        $_GET['q'] = 'dvd';
        $_GET['file'] = 'somefile.php';
     
        //$_GET['enable'] = '1';
        Settings::setLocal('enabled','1',1);
             
        $rank_prior = vdo::webshop(1)->fetchOne("select rank from words where word = 'dvd'");
        $this->assertNotNull($rank_prior);
      // echo "dddddd";
     
        $this->assertNotEquals(false, $rank_prior);
       
      //  $_GET['max_price'] = 120;
        $_GET['postvar'] = json_encode(array('min_price'=>300,'hohn'=>3,4,5));
      //  $_GET['getvar'] = json_encode(array('eq_options'=>array('category'=>array(1,2,3,4)),'nameff'=>3,4,5));
      
       Settings::setLocal('ranked_search_treshold','101',1);
        Settings::setLocal('search_result_output_format','html',1);
        
        $h = new SearchHandler();
      // echo "exi2"; exit;
        $res = $h->getOutput();
        //$this->assertEquals('dvd',$h->searcher->original);
      
        $this->assertInternalType('string',$res);
       
        
        $this->assertNotContains('The Replacement Killers',$res); // Should not contain this
        $this->assertContains('Matrox',$res, $res); // Must contain this
        $this->assertNotContains('Under Siege 2 - Dark Territory',$res); // 
        $rank_after = vdo::webshop(1)->fetchOne("select rank from words where word = 'dvd'");
        $this->assertEquals($rank_prior +1 ,$rank_after );
    }
    
     function testResultsHTMLWithslightMinPrice()
    {
        $_GET['host'] = 'everlight.dk';
        $_GET['q'] = '';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 1;
   
      //  $_GET['max_price'] = 120;
        $_GET['postvar'] = json_encode(array('min_options'=>array('lowest_price'=>"1"),'max_options'=>array('lowest_price'=>'2')));
       //  $_GET['getvar'] = json_encode(array('eq_options'=>array('category'=>array(1,2,3,4)),'nameff'=>3,4,5));
        Settings::setLocal('search_result_output_format','html',1);
        $h = new SearchHandler();
        $res = $h->getOutput();
        //$this->assertEquals('dvd',$h->searcher->original);
        
        $this->assertInternalType('string',$res);
       
        /// Those two below must certaintly fail
        $this->assertNotContains('The Replacement Killers',$res); 
        $this->assertNotContains('Matrox',$res); // 
        $this->assertNotContains('Under Siege 2 - Dark Territory',$res); // 
    }
    
    function testResultsHTMLWithMinMax()
    {
        $_GET['host'] = 'everlight.dk';
        $_GET['q'] = '';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 1;
   
      //  $_GET['max_price'] = 120;
        $_GET['postvar'] = json_encode(array('min_options'=>array('lowest_price'=>"0"),'max_options'=>array('lowest_price'=>'1000')));
       //  $_GET['getvar'] = json_encode(array('eq_options'=>array('category'=>array(1,2,3,4)),'nameff'=>3,4,5));
        Settings::setLocal('search_result_output_format','html',1);
        $h = new SearchHandler();
        $res = $h->getOutput();
        //$this->assertEquals('dvd',$h->searcher->original);
        
        $this->assertInternalType('string',$res);
       
        /// Those two below must certaintly fail
        $this->assertContains('The Replacement Killers',$res); 
        $this->assertContains('Matrox',$res); // 
        $this->assertContains('Under Siege 2 - Dark Territory',$res); // 
    }
    
    function testResultsHTMLWith0MinPrice()
    {
        $_GET['host'] = 'everlight.dk';
        $_GET['q'] = '';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 1;
   
      //  $_GET['max_price'] = 120;
        $_GET['getvar'] = json_encode(array('min_options'=>array('lowest_price'=>"0"),'max_options'=>array('lowest_price'=>'2')));
       //  $_GET['getvar'] = json_encode(array('eq_options'=>array('category'=>array(1,2,3,4)),'nameff'=>3,4,5));
        Settings::setLocal('search_result_output_format','html',1);
        $h = new SearchHandler();
        $res = $h->getOutput();
        //$this->assertEquals('dvd',$h->searcher->original);
        
        $this->assertInternalType('string',$res);
       
        /// Those two below must certaintly fail
        $this->assertNotContains('The Replacement Killers',$res); 
        $this->assertNotContains('Matrox',$res); // 
        $this->assertContains('Under Siege 2 - Dark Territory',$res, print_r($res,true)); // 
    }

 

    
    function testNothingShouldBeFound()
    {
        $_GET['host'] = 'everlight.dk';
        $_GET['q'] = 'dvjaslkfjsdflækjasfælkjsdfælkjsdæklfjdsalækfjsadælkfjsadælkfjælksadjfaklsæfd';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 1;
   
      //  $_GET['max_price'] = 120;
      //  $_GET['postvar'] = json_encode(array('min_price'=>300,'hohn'=>3,4,5));
      //  $_GET['getvar'] = json_encode(array('eq_options'=>array('category'=>array(1,2,3,4)),'nameff'=>3,4,5));
        Settings::setLocal('search_result_output_format','html',1);
        $h = new SearchHandler();
        $res = $h->getOutput();
        //$this->assertEquals('dvd',$h->searcher->original);
        
        $this->assertInternalType('string',$res);
       
        /// Those two below must certaintly fail
        $this->assertNotContains('The Replacement Killers',$res); 
        $this->assertNotContains('Matrox',$res); // 
    }
    
    function testNothingShouldBeFoundWithSort()
    {
        $_GET['host'] = 'everlight.dk';
        $_GET['q'] = 'dvjaslkfjsdflækjasfælkjsdfælkjsdæklfjdsalækfjsadælkfjsadælkfjælksadjfaklsæfd';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 1;
        $_GET['sort_by'] = 'price@asc';
      //  $_GET['max_price'] = 120;
      //  $_GET['postvar'] = json_encode(array('min_price'=>300,'hohn'=>3,4,5));
      //  $_GET['getvar'] = json_encode(array('eq_options'=>array('category'=>array(1,2,3,4)),'nameff'=>3,4,5));
        Settings::setLocal('search_result_output_format','html',1);
        $h = new SearchHandler();
        $res = $h->getOutput();
        //$this->assertEquals('dvd',$h->searcher->original);
        
        $this->assertInternalType('string',$res);
       
        /// Those two below must certaintly fail
        $this->assertNotContains('The Replacement Killers',$res); 
        $this->assertNotContains('Matrox',$res); // 
    }

    function testVatIsDonePropelry()
    {
        $_GET['host'] = 'everlight.dk';
        $_GET['q'] = 'Reinforcing';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 1;
        Settings::setLocal('search_result_output_format','html',1);
        settings::setLocal('vat_multiplyer','1.25',1);
        settings::setLocal('prices_stored_with_vat','1',1);
        $h = new SearchHandler();
        $res = $h->getOutput();
        $this->assertEquals($_GET['vubla_enable_vat'], true);
        $this->assertContains('Matrox',$res, $res); // Must contain this
        $this->assertContains('299,99',$res,$res); 
        
        settings::setLocal('vat_multiplyer','1.25',1);
        settings::setLocal('prices_stored_with_vat','0',1);
        $h = new SearchHandler();
        $res = $h->getOutput();
        $this->assertContains('374,9',$res); 
        

        
        settings::setLocal('vat_multiplyer','1.25',1);
        settings::setLocal('prices_stored_with_vat','0',1);
        $_GET['vat_disp'] = 0;
        $h = new SearchHandler();
        $res = $h->getOutput();
        $this->assertContains('299,99',$res); 
        
        settings::setLocal('vat_multiplyer','1.25',1);
        settings::setLocal('prices_stored_with_vat','1',1);
        $_GET['vat_disp'] = 0;
        $h = new SearchHandler();
        $res = $h->getOutput();
        $this->assertContains('239,99',$res); 
        
        settings::setLocal('mage_store_id_without_vat','asdf',1);
        settings::setLocal('vat_multiplyer','1.25',1);
        settings::setLocal('prices_stored_with_vat','0',1);
        $_GET['store_id'] = 'asdf';
        $h = new SearchHandler();
        $res = $h->getOutput();
        $this->assertEquals($_GET['vubla_enable_vat'], false);
        $this->assertContains('299,99',$res); 
   
    }
    
    function testOptions2(){   
        $vars = array('min_options'=>array('pid'=>3),'max_options'=>array('pid'=>5));
        $getpostvars = json_encode($vars);
        
        $_GET['q'] = '';
        $_GET['postvar'] = $getpostvars;
        $this->runTestOnOptions();
        unset( $_GET['postvar'] );
        
        $_GET['getvar'] = $getpostvars;
        $this->runTestOnOptions();
        unset( $_GET['getvar'] );
        
        $_GET = $vars;
        $_GET['q'] = '';
        $this->runTestOnOptions();
        
        unset($_GET);
        $_POST = $vars;
        $_POST['q'] = '';
        $_POST['host'] = 'everlight.dk';
        $this->runTestOnOptions();
        
        
    }
    
    
function testActiveSliderFalseHidesPostVars()
{   
        $vars = array('min_options'=>array('pid'=>3),'max_options'=>array('pid'=>5),'pid_slider_active'=>'no');
        $getpostvars = json_encode($vars);
        $_GET['host'] = 'everlight.dk';
        $_GET['q'] = '';
        $_GET['postvar'] = $getpostvars;
        
        Settings::setLocal('search_result_output_format','ids',1);
        $h = new SearchHandler();
        $res = json_decode($h->getOutput(),true);
        $this->assertCount(28,(array)$res['ids']);
        
    }
        
function testActiveSliderFalseHidesGET()
{   
        $vars = array('min_options'=>array('pid'=>3),'max_options'=>array('pid'=>5),'pid_slider_active'=>'no', 'q'=>'');
      
        $_GET = $vars;
       $_GET['host'] = 'everlight.dk';
        Settings::setLocal('search_result_output_format','ids',1);
        $h = new SearchHandler();
        $res = json_decode($h->getOutput(),true);
        $this->assertCount(28,(array)$res['ids']);
        
    }
    
    
    function runTestOnOptions()
    {
        $_GET['host'] = 'everlight.dk';
        Settings::setLocal('search_result_output_format','ids',1);
        $h = new SearchHandler();
        $res = json_decode($h->getOutput(),true);
        $this->assertCount(3,$res['ids']);
        $this->assertContains(3,$res['ids'] ,print_r($res, true));
        $this->assertContains(4,$res['ids']);
        $this->assertContains(5,$res['ids']);
    }
    
/*
    function testWrongHostnameFallBack()
    {
        $_GET['host'] = 'ratardethostname.dk';
        $_GET['q'] = 'dvd';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 1;
        $_GET['sort_by'] = 'price@asc';
        ob_start();
        $h = new SearchHandler();
        $res = $h->getOutput();
        //$this->assertEquals('dvd',$h->searcher->original);
        
        $ob = ob_get_clean();
        $this->assertEquals('',$ob);
    
    }
  */
  
}

