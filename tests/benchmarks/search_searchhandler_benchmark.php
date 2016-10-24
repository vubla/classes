<?php

require '../vublamailer.php';
require '../basedbtest.php';
define('WID',2);
$suite  = new PHPUnit_Framework_TestSuite("SearchHandlerTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);



class SearchHandlerBenchmark extends Searcher
{
    var $min_opt;
    var $max_opt;
    var $eq_opt;
    
    function __construct(){
        $ref = "";
        parent::__construct(2,$ref);
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
    
    var $start;
    var $total;
    var $stopped;
    var $running;
    var $wid = 2;
    function setUp() 
    {
        
        $this->buildDatabases();
        Settings::setLocal('ranked_search_threshold',-1,$this->wid);
        Settings::setLocal('api_out',1,$this->wid);
      
        $_GET = array();
      

    }

    function tearDown() 
    {

        $this->dropDatabases();
       
        $_GET = array();
    }
    
    /*
    function testBenchSearcherI(){
        $_GET['host'] = 'phpunit_2';
        $_GET['q'] = 'i';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 0;
        $loops = 1;
        Settings::setLocal('enabled',1,$this->wid);
        $this->startTimer();
         $mdo = VPDO::getVdo(DB_PREFIX.$this->wid);
        for($i = 0; $i < $loops; $i++){
           
            $h = new Searcher( $_GET['q'] );
            $searcherTimer = new IdHandleTimer($h);
            $result = $searcherTimer->getResults(array());
            $result->total_search_time = $searcherTimer->getTime();

            $log = new SearchLog($result);
            $log->saveNew($mdo);
            
          
            
            
        }
        
        $this->stopTimer();
        
        $this->saveSearchLog($loops, 'searcher_i');
      
        
    }
   */
    function testBenchSearchhandlerI(){
        $_GET['host'] = 'phpunit_2';
        $_GET['q'] = 'i';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 0;
       
        $this->searchHandlerBenchmark('searchhandler_i');
        
    }
    
    function testBenchSearchhandlerIOption(){
        $_GET['host'] = 'phpunit_2';
        $_GET['q'] = 'I';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 0;
        $_GET['eq_options'] = json_encode(array('category'=>array('Bil')));
        $this->searchHandlerBenchmark('searchhandler_i with options');
        
    }
    
    function testBenchSearchhandlerIOptionMultipleCats(){
        $_GET['host'] = 'phpunit_2';
        $_GET['q'] = 'I';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 0;
        $_GET['eq_options'] = json_encode(array('category'=>array('Bil','Visker gummi','Xenon kit til MC')));
        $this->searchHandlerBenchmark('searchhandler_i with multiple cats');
        
    }
    
    
    function testBenchSearchhandlerIWithPriceOptions(){
        $_GET['host'] = 'phpunit_2';
        $_GET['q'] = 'I';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 0;
        $_GET['min_options'] = json_encode(array('lowest_price'=>'37'));
        $_GET['max_options'] = json_encode(array('lowest_price'=>'370'));
        $this->searchHandlerBenchmark('searchhandler_i with price options');
        
    }
    
    function testBenchSearchhandlerISortPrice(){
        $_GET['host'] = 'phpunit_2';
        $_GET['q'] = 'I';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 0;
        $_GET['sort_by'] = 'lowest_price@asc';
        $this->searchHandlerBenchmark('searchhandler_i sort by price');
        
    }
    
    function testBenchSearchhandlerISortName(){
        $_GET['host'] = 'phpunit_2';
        $_GET['q'] = 'I';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 0;
        $_GET['sort_by'] = 'name@asc';
        $this->searchHandlerBenchmark('searchhandler_i sort by name');
        
    }
    
    
    function testBenchSearchhandlerIShowAlll(){
        $_GET['host'] = 'phpunit_2';
        $_GET['q'] = 'I';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 0;
        $_GET['show_all'] = 'On';
        $this->searchHandlerBenchmark('searchhandler_i show all');
        
    }
    
  
     function testBenchSearchhandlerXenon(){
        $_GET['host'] = 'phpunit_2';
        $_GET['q'] = 'Xenon';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 0;
      
        $this->searchHandlerBenchmark('searchhandler_xenon');
        
    }
     
       function testBenchSearchhandlerCombined(){
        $_GET['host'] = 'phpunit_2';
        $_GET['q'] = 'Xenon';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 0;
        $_GET['min_options'] = json_encode(array('lowest_price'=>'37'));
        $_GET['max_options'] = json_encode(array('lowest_price'=>'370'));
        $_GET['eq_options'] = json_encode(array('category'=>array('Bil')));
        $_GET['show_all'] = 'On';
        $_GET['sort_by'] = 'name@desc';
        $this->searchHandlerBenchmark('searchhandler_I xcombined');
        
    }
     
    function testBenchSearchhandlerMultipleWords(){
        $_GET['host'] = 'phpunit_2';
        $_GET['q'] = 'Xenon kit h7 xst';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 0;
        $this->searchHandlerBenchmark('searchhandler_i multiple words');
        
    }
    
    function testBenchSearchhandlerMultipleWordsXtreme(){
        $_GET['host'] = 'phpunit_2';
        $_GET['q'] = 'Xenon kit h7 xst i o pærer dvd';
        $_GET['file'] = 'somefile.php';
        $_GET['enable'] = 0;
        $this->searchHandlerBenchmark('searchhandler_i multiple words extreme');
        
    }
    
    
    function searchHandlerBenchmark($name){
        $loops = 5;
        Settings::setLocal('enabled',1,$this->wid);
        $mdo = VPDO::getVdo(DB_PREFIX.$this->wid);
        $this->startTimer();
        
        for($i = 0; $i < $loops; $i++){
            $h = new SearchHandler();
            $result = $h->getOutput();
             
        }
        $this->stopTimer();
        
        $this->saveSearchLog($loops, $name);
      
    }
    
    function saveSearchLog($loops, $name){
        $total = ($this->getTime() * 1000000)/$loops;
        $rev = exec("cd ".CLASS_FOLDER."; hg summary | head -c 11 | tail -c 3");
        $mdo = VPDO::getVdo(DB_PREFIX.$this->wid);
        $vdo = VPDO::getVdo('phpunit_main');
        //$temp =$mdo->getRow("select * from ( select (`option_filter_time`) as option_time, (`sorting_time`) as sorting, (`widget_factory_time`) as widget, (`string_filter_timer`) as string,(`product_factory_time`) as factory, (`total_search_time`) as totsearch from search_log order by id desc limit   ".$loops.') as john');
       // var_dump($temp);
       
          $slog = $mdo->getRow("select 
                                    avg(option_time) as option_filter_time, avg(sorting) as sorting_time, avg(widget) as widget_factory_time, avg(string) as string_filter_timer, avg(totsearch) as total_search_time, avg(`factory`) as product_factory_time 
                                from ( select (`option_filter_time`) as option_time, (`sorting_time`) as sorting, (`widget_factory_time`) as widget, (`string_filter_timer`) as string,(`product_factory_time`) as factory, (`total_search_time`) as totsearch from search_log order by time desc limit   ".$loops.') as john');
     
        $vdo->exec("INSERT INTO `phpunit_main`.`benchmarks` (`name`, `option_filter_time`, `sorting_time`, `widget_factory_time`, `string_filter_time`,`product_factory_time`,  `total_search_time`,`total`, `rev`) VALUES ('$name', '$slog->option_filter_time', '$slog->sorting_time', '$slog->widget_factory_time', '$slog->string_filter_timer','$slog->product_factory_time','$slog->total_search_time', '$total', '$rev');");
         
        
    }
    
     function startTimer()
    {
        $this->running = true;
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $this->start = $time;   
    }
    
    function stopTimer()
    {
        if(!$this->running){
            throw new Exception("Timer was never started", 1);  
        }
        $this->running = false;
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $this->stopped = $time;
        $this->total = round(($this->stopped  - $this->start), 4);
    }
    
    function getTime()
    {
        return $this->total;
    }
  
}

