<?php


class SearchHandler extends AnySearchObject
{
    var $host;
    var $q;
    var $shopFile;
    var $searchParam = 'keywords';
    var $searcher;
    var $logthis = true;
    var $realHostName;
    var $meta;
    var $wid;
    var $wpdo;
    var $isEnabled;
    
    function __construct()
    {
        if(ob_get_level() < 1)
        {
            ob_start();
        }
        $this->meta = VPDO::getVdo(DB_METADATA);
        $this->initialize();
        
        if($this->wid){
            parent::__construct($this->wid);
            
            $solr = settings::get('enable_solr_search', $this->wid);
            if(array_key_exists('solr', $_GET))
            {
                $solr = $_GET['solr'];
            }
            elseif (array_key_exists('ip', $_GET) && strlen($_GET['ip']) >= 7 && strpos(settings::get('ips_use_solr', $this->wid), $_GET['ip']) !== FALSE) 
            {
                $solr = 1;
            }
            if($solr)
            {
                $this->searcher = new SolrSearcher($this->wid,$this->q, $this->logthis); 
            }
            else
            {
                $this->searcher = new Searcher($this->wid,$this->q, $this->logthis);
            }   
            $this->handleOptions();
            $this->handleSortBy();
            $this->wpdo = VPDO::getVdo(DB_PREFIX . $this->wid);
            $this->determineVat();
            
        }
    }
    
    private function ajaxSearch()
    {
        return $this->ajax_only_results || $this->suggestions || $this->ajax_full_search;
    }
    
    function inputEncode($q){
        
        $from_encoding = Settings::get('encode_from', $this->wid);
        $vubla_encoding = Settings::getGlobal('vubla_encoding');
        if(!isset($_GET['enable']) && isset($from_encoding) && $from_encoding != $vubla_encoding && !$this->ajaxSearch()){
            $q = iconv($from_encoding, $vubla_encoding, $q);
        }
        return $q;
    }
    
     function outputEncode($q){
        $from_encoding = Settings::get('encode_from', $this->wid);
        $vubla_encoding = Settings::getGlobal('vubla_encoding');
        if(!isset($_GET['enable']) && isset($from_encoding) && $from_encoding != $vubla_encoding){
            $q = iconv( $vubla_encoding, $from_encoding,$q);
        }
        return $q;
    }
    
    
        
    function initialize()
    {			
        if(isset($_POST) && !empty($_POST)) {
        	$_GET = $_POST; // I dont know about this :/
        }
        if(!isset($_GET['host']) ||  !$_GET['host']){
           throw new SearchException("No Hostname given. got: ". print_r($_GET,true)." \n" . ob_get_contents());
         
        }
        $this->host = $_GET['host'];
        $this->wid = $this->resolveWid($this->host);
        if($this->wid == 0){
            return;
        }
        $this->realHostName = $_GET['host'] = $this->getRealHostname();
        if(!isset($_GET['q']))
        {
            $_GET['q'] = "";
        }
       
        $this->q = $_GET['q'];
        if($this->host != 'dev.med24.dk' && $this->host != 'med24.dk'){
            /// For some weird reason this is not needed on med24.dk
            $this->q = $this->inputEncode(urldecode( $this->q));
        } else {
            @ $getvar = json_decode($_GET['getvar']);
            if (is_object($getvar) && $getvar->ie == 'ISO-8859-1'){
                $this->q = ($this->inputEncode( $this->q));
            }
        }
        $this->q = strip_tags(($this->q)); 
        // File is used to specify the file used relative to host root for the did you mean.
        $file = '';
        if(isset($_GET['file'])){
            $this->shopFile = $_GET['file'];
        }
    
   
      
        @$_GET['postvar'] = json_decode($_GET['postvar']);
        @$_GET['getvar'] = json_decode($_GET['getvar']);

        // Used for did you mean
        if(isset($_GET['param'])){
            $this->searchParam = $_GET['param'];
        }
     
        @define('IP',$_GET['ip']);
        @define('USERAGENT', $_GET['useragent']);

        $splitTestUseVubla = true;


        try {
            $splitTester = new Day2DaySplitTestHandler($this->wid);
            $splitTestUseVubla = $splitTester->useVubla();
        } catch(VublaException $err) {
            VublaMailer::sendOnargiEmail('Split Test Error', 'It failed for host '. $this->host .' with wid '. $this->wid .'<br />Error: '.$err.'<br />We use vubla search as default to continue.<br /><pre>'.ob_get_contents().'</pre>');
        }
        
        
		
        $isEnabled = Settings::get('enabled',$this->wid) && $splitTestUseVubla;
        @$getEnabled = $_GET['enable'] || $_GET['getvar']->enable;
        if(!$isEnabled && !$getEnabled) {
            exit();
        }
        
        $this->isEnabled = true;
        
        if($getEnabled && !(defined('VUBLA_DEBUG') && VUBLA_DEBUG)){
            $this->logthis = false;   
        }
    }

    /**
     * Determines wheter vat should be present or not. 
     */
    function determineVat()
    {
        $non_vat_identifier = array('vat_disp'=>false,'vat_display'=>false,'display_vat'=>false);
        $_GET['vubla_enable_vat'] = true;
        foreach($non_vat_identifier as $vat_name=>$should_be)
        {
            if(!is_null($this->$vat_name) && $this->$vat_name == $should_be)
            {
                $_GET['vubla_enable_vat'] = false;
            }
        }
        if($this->store_id == settings::get('mage_store_id_without_vat', $this->wid)){
            $_GET['vubla_enable_vat'] = false;
        }
    }

    function getRealHostname()
    {
        $sql2 = "Select hostname from webshops where id = ? limit 1";
        $real_host = $this->meta->fetchOne($sql2,array($this->wid) );
        return $real_host;
    }
    
    function handleOptions(){
     /*   var_dump($_GET);
        var_dump($_POST); exit; */
     
        $maxOptionsArray = array();
        $minOptionsArray = array();
        $eqOptionsArray = array();
        ##########################################
        # MIN AND MAX PRICE
        ##########################################
        if(($this->pfrom)){
            $this->min_price = (int)$this->pfrom;
        }

        if(($this->pto)){
            $this->max_price = (int)$this->pto;
        }
      
    
         ##############################
        # OPTIONS AND CATEGORIES
        ##############################
        
        $categoriesArray = null;
        $optionsArray = null;
      
    
        if(!is_null($this->max_options)){
           
            if(is_string($this->max_options)){
                $temp_max_option = json_decode($this->max_options,true); 
            } 
            else 
            {
                $temp_max_option =(array) $this->max_options; 
            }
              // return;
         // echo var_dump($optionsArray);
            //$temp_max_option = json_decode($this->max_options, true);
            //return;
            if($err = json_last_error() != JSON_ERROR_NONE){
               /**
                * If an error occors we still present the user results, but we make sure we get notified.
                */
                VublaMailer::sendOnargiEmail('Json could not be decoded', 'It failed for host '. $this->host .' with wid '. $this->wid .'<br />'.$err.'<br /><pre>'.ob_get_contents().'</pre>');  
            }
            if(!is_array($maxOptionsArray)) {
                VublaMailer::sendOnargiEmail('Maxoptions was not an array', 'It failed for host '. $this->host .' with wid '. $this->wid .'<br />' . $_GET['max_options'] .'<br/><pre>'.ob_get_contents().'</pre>');
                $maxOptionsArray = array();
            }
            $maxOptionsArray = $temp_max_option;
        
        } 
        //echo urlencode(json_encode(array("product_price"=>100)));
       // return;
        if(!is_null($this->min_options))
        {
            if(is_string($this->min_options))
            {
                $temp_min_option = json_decode($this->min_options,true); 
            } 
            else 
            {
                $temp_min_option =(array) $this->min_options; 
            }
         // echo var_dump($optionsArray);
            if($err = json_last_error() != JSON_ERROR_NONE)
            {
               /**
                * If an error occors we still present the user results, but we make sure we get notified.
                */
               VublaMailer::sendOnargiEmail('Json could not be decoded', 'It failed for host '. $this->host .' with wid '. $this->wid .'<br />'.$err.'<br /><pre>'.ob_get_contents().'</pre>');  
            }
            
            if(!is_array($temp_min_option)) 
            {
                VublaMailer::sendOnargiEmail('Minoptions was not an array', 'It failed for host '. $this->host .' with wid '. $this->wid .'<br />' . $_GET['min_options'] .'<br/><pre>'.ob_get_contents().'</pre>');
                $minOptionsArray = array();
            }
             $minOptionsArray = $temp_min_option;
        } 
 
        if(!is_null($this->eq_options))
        { 
            if(is_string($this->eq_options))
            {
                $temp_eq = json_decode($this->eq_options,true); 
            } 
            else 
            {
                $temp_eq = (array)$this->eq_options; 
            }
            $_GET['eq_option'] = $temp_eq; // We save them again in the get to make sure they can be retrieved as arrays later           
       
       
            
       
            if($err = json_last_error() != JSON_ERROR_NONE){
               /**
                * If an error occors we still present the user results, but we make sure we get notified.
                */
               VublaMailer::sendOnargiEmail('Json could not be decoded', 'It failed for host '. $this->host .' with wid '. $this->wid .'<br />'.$err.'<br /><pre>'.ob_get_contents().'</pre>');  
            }
            
         
            if(!is_array($temp_eq)) 
            {
                VublaMailer::sendOnargiEmail('Eq_options was not an array', 'It failed for host '. $this->host .' with wid '. $this->wid .'<br />' . print_r($this->eq_options,true).'<br/><pre>'.ob_get_contents().'</pre>');
                $eqOptionsArray = array();
            }
            
  //     var_dump( $temp_eq['category']); exit;
            if(isset($temp_eq['category']))
            {
                $temp_eq['category'] = $this->fixCatsAndKillParents( $temp_eq['category']);
            }
            if(isset($temp_eq['category_id']))
            {
                $temp_eq['category_id'] = $this->fixCatsAndKillParents( $temp_eq['category_id']);
            }

            $eqOptionsArray = $temp_eq;
        } 
     
      
        // The following two ifs were commented out for some reason. I removed them and tests passed.
        if(!is_null($this->max_price) && $this->max_price > 0)
        {
            $maxOptionsArray['lowest_price'] = (int)$this->max_price;
        }
        
        if(!is_null($this->min_price))
        {
            $minOptionsArray['lowest_price'] = (int)$this->min_price;
        }
      

        if(!is_null($this->max_lowest_price) && $this->max_lowest_price > 0)
        {
            $maxOptionsArray['lowest_price'] = (int)$this->max_lowest_price;
        }
        
        if(!is_null($this->min_lowest_price))
        {
            $minOptionsArray['lowest_price'] = (int)$this->min_lowest_price;
        }
      
      
       ///exit;
        //$this->searcher->setCategories($categoriesArray);
        $this->searcher->setMaxOptions($maxOptionsArray);
        $this->searcher->setMinOptions($minOptionsArray);
        $this->searcher->setEqOptions($eqOptionsArray);
    }
    
    function fixCatsAndKillParents($array){
            /*
             * Following removes parents from their childs if needs to, and only in categories
             */
  
                
            $parents_to_be_killed = array();
            $array = (array) $array;
            foreach ($array as $key=>$value) 
            {
                if(strpos($value,'@') !== false )
                {
                    list($new_value, $parents_to_be_killed[]) = explode('@', $value);
                }
                else 
                {
                    $new_value = $value;
                }
                $arr[$key] = $new_value;
                
            }
        
            return array_values(array_diff(array_values($arr), array_values(array_unique($parents_to_be_killed))));
           
        
        
    }
    
    function handleSortBy()
    {
  
        if(($this->sort_by))
        {
            if(strpos($this->sort_by,'@') !== false)
            {
                list($this->sort_by, $tsortorder) = explode('@',$this->sort_by);
            } 
          
            if(!($this->sortorder))
            {
                $this->sortorder = $tsortorder;
            }
     //       var_dump($this->)
            $this->searcher->setSortBy($this->sort_by);
            $this->searcher->setSortOrder($this->sortorder);
        }
        
    }
    
    function getOutput()
    {
        if($this->wid == 0){
            ob_clean();
            return;
        }
        if(!$this->isEnabled){
            return;
        }
        
        $this->search_type = 'normal';
        if(isset($_POST['suggestions']) || isset($_GET['suggestions'])) {
            $this->search_type = 'suggestions';
        }
        else if(isset($_POST['api_suggestions']) || isset($_GET['api_suggestions'])) {
            $this->search_type = 'api_suggestions';
        }
        else if(Settings::get('api_out',$this->wid) == 1) {
            $this->search_type = 'api_out';
        }
           
        try 
        {
            switch ($this->search_type) {
            case 'normal':
                return $this->getNormalOutput();
                break;
            case 'suggestions':
                return $this->getSuggestionsOutput();
                break;
            case 'api_suggestions':
                return $this->getSuggestionAPIOutput();
                break;
            case 'api_out':
                return $this->getAPIOutput();
                break;
            default:
                break;
            }
        } catch (SearchException $e)
        {
            throw  $e;
        }
    }
	
	private function getSuggestionsOutput() {
        $filter = new FastStringFilter($this->wid,$this->q,true);
        $factory = new ProductFactory($this->wid);
        $ids = $filter->getResults(array());
        $result = $factory->getResults($ids);

        $temp = new stdClass();
        $temp->results = $result;
        $temp->q = $this->q; 
        $out =  json_encode($temp);
	    try {
            $this->verifyoutput($out, $result);
        } catch(Exception $e){
             throw $e;
        }

		return $out;
	}
    
    private function getSuggestionAPIOutput() {
        $filter = new FastStringFilter($this->wid,$this->q,true);
        $factory = new DummyProductIdHandler($this->wid);
        $ids = $filter->getResults(array());
        $result = $factory->getResults($ids);

        $temp = new stdClass();
        $temp->products = $result;
        $temp->q = $this->q; 
        $out =  json_encode($temp);
        try {
            $this->verifyoutput($out, $result);
        } catch(Exception $e){
             throw $e;
        }

        return $out;
    }
	
    private function getNormalOutput() {
		
        $searcherTimer = new IdHandleTimer($this->searcher);
        $result = $searcherTimer->getResults(array());
        if(!is_null($this->vubla_debug)){
            print_r($result); exit;
        }
          
        $result->total_search_time = $searcherTimer->getTime();
        if($this->logthis){
            $log = new SearchLog($this->wid,$result);
            $log->saveNew($this->wpdo);
        }
		
        if(!is_object($result))
        {
            throw new SearchException('Results was not an object');
        }

        if(isset($_GET['ajax_only_results'])) {
            $this->output_format = 'ajax_only_results';
        }
        if(isset($_GET['ajax_full_search'])) {
            $this->output_format = 'ajax_full_search';
        }
        if(is_null($this->output_format)){
            $this->output_format = Settings::get('search_result_output_format',$this->wid);
        }

        switch($this->output_format){
        case 'json':
            $out = json_encode($result);
             break;
        case 'ids':
            $out = new stdClass();
            $out->ids = array();
            $out->keywords = array();
            $out->alternatives = array();
            if(isset($result->products)) {
                foreach ($result->products as $product) {
                    if(isset($product->pid)) {
                        $out->ids[] = $product->pid;
                    }
                }

                $out->ids = array_unique($out->ids);
            }
            if(isset($result->userdefinedkeywords)) {
                foreach ($result->userdefinedkeywords as $keyword) {
                    if(isset($keyword->url) && isset($keyword->text)) {
                        $temp = new stdClass();
                        $temp->url  = $keyword->url;
                        $temp->text = $keyword->text;
                        $out->keywords[] = $temp;
                    }
                }
            }
            if(isset($result->alternatives)) {
                foreach ($result->alternatives as $alternative) {
                    if(isset($alternative)) {
                        $temp = new stdClass();
                        $temp->word = $alternative;
                        $out->alternatives[] = $temp;
                    }
                }
            }
            $out = json_encode($out);
            break;
        case 'ajax_only_results':
            $out = Template::generateCurrentTemplateResultsForAJAX($this->wid,$this->realHostName,$result);
            break;
        case 'ajax_full_search':
            $out = Template::generateCurrentVisualTemplateResults($this->wid,$this->realHostName,$result);
            break;
        default:
            $out = Template::generateCurrentTemplate($this->wid,$this->realHostName,$result);
        }
    
    
    
        if(!isset($_GET['on_vubla_site']) && $this->output_format != 'ajax_only_results'  && $this->output_format != 'ajax_full_search')
        { 
          $out = $this->outputEncode($out);
         //  $out = $this->q.print_r($this->output_format, true)."Encoded". $out ;
        } 
        try {
            $this->verifyoutput($out, $result);
        } catch(SearchException $e){
              throw $e;
        }
        VublaLog::killGently();  // We write to the log at many points and want it to send on kills, but not this time.
        
        if($this->isEnabled){
            return $out;
        }
	}

    private function getAPIOutput() {
        $innerHandler = new ApiOutputHandler($this->wid);
        return $innerHandler->getOutput($this->searcher, $this->logthis);
    }
    
    function verifyoutput($out, $result)
    {
        $ob = ob_get_contents();
        if(defined('DEBUG_SEARCH') && DEBUG_SEARCH){
            VublaLog::_n("Host ". $this->host);
            VublaLog::_n("wid". $this->wid);
            VublaLog::_n("Q ". $this->searcher->original);
            VublaLog::_n("Out" . $out);
            VublaLog::_n("Getting ob flush from search: \n" . nl2br($ob));
            VublaLog::_n("<pre>".print_r($this->searcher->errors, true)."</pre> \n");
            VublaLog::output();
            //exit;
        }
        else 
        {
            
            /*#############################
            #LOG AND TERMINATIION
            #############################*/
            if($this->searcher->errors){
                  throw new SearchException('Search contained errors');
            }
        
            if($ob){
                  throw new SearchException('Ob not empty '. print_r($ob, true));
            }

        }
    }

    public function __get($name){
        if(isset($this->$name)){
            return $this->$name;
        } elseif(isset($_GET[$name])) {
            $ss = $_GET[$name];
            return $ss;
        }  elseif(isset($_GET['getvar']->$name)){
           $ss =  $_GET['getvar']->$name;
           return $ss;
        } elseif(isset($_GET['postvar']->$name)){
           $ss =  $_GET['postvar']->$name;
           return $ss;
        }
        return null;
    }
}
