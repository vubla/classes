<?php

/*
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}

/// ENCODING 
/// The crawler does not care about encoding!!!

/* The Crawler
 * Gets content from webshops and delivers it in XML to the productFinder
 *
class Crawler {
    
    protected $host;
    protected $wid;
    protected $db_meta;
    protected $productProduct;
    protected $customer_debug;
    protected $notes = ''; 
    const CRAWL_INTERVAL = 604800;
    const MAX_TIME_TO_BE_CRAWLED = 700000;
    const NO_CONNECTION = 2; 
    const SUCCESS = 4;
    const VUBLA_ERROR = 3; 
    const FAILED_MAGENTO_LOGIN = 5;
	const ALREADY_CRAWLING = 6;
    function __construct($wid){
        if(!defined('VUBLA_DEBUG')) define('VUBLA_DEBUG', false);
        if($wid < 1){
            echo "No Wid in crawler"; 
            exit;
        }
        $this->wid = (int)$wid;
        $this->productFinder = new ProductFinder($this->wid);
        $this->db_meta =  VPDO::getVdo(DB_METADATA);
        
    }


    public function crawl(){
    	if(!VUBLA_DEBUG){
    		@ob_start();
		}

        
       $this->setCurrentlyBeingCrawled(1);
        //Settings::setLocal('currently_being_crawled',1, $this->wid);  
      
      
        $this->host = $this->db_meta->fetchOne('select hostname from webshops where id='.(int)$this->wid.' limit 1');
        if(is_null($this->host)){
            VublaMailer::sendOnargiEmail('No host in crawl', 'select hostname from webshops where id='.$this->wid.' limit 1<br /><br /><pre>'.ob_get_clean().'<br /><br />'.print_r($e,true).'</pre>');
            $this->setCurrentlyBeingCrawled(0);
            if(!VUBLA_DEBUG){
                @ob_end_clean();
            }
            return self::VUBLA_ERROR;
        }
        
        $webshopType = $this->db_meta->fetchOne('select type from webshops where id = ?',array( $this->wid));
        
        
        switch($webshopType){
            case 1: 
                
                if(VUBLA_DEBUG) echo 'oscommerce';
                $wentWwell = $this->rawCrawl();   
                break; 
            case 2: // Magento 
                if(VUBLA_DEBUG) echo 'magento';
                $magento = new MagentoClient($this->host, $this->wid);
                
                if($magento->getErrorState() == self::FAILED_MAGENTO_LOGIN){
                    $this->setCurrentlyBeingCrawled(0);
                    if(!VUBLA_DEBUG){ @ob_end_clean();    }
                    return self::FAILED_MAGENTO_LOGIN;
                } else {
                    $wentWwell = $this->soapCrawl($magento);  
                }
                break;    
            case 3: // Presta
                if(VUBLA_DEBUG) echo 'presta';
                $wentWwell= $this->rawCrawl();
                break;
            case 4: // Others
                if(VUBLA_DEBUG) echo 'others';
                $wentWwell = $this->rawCrawl();
                
            case 5:
                if(VUBLA_DEBUG) echo 'smartweb';
                $smartWeb = new SmartWebClient($this->host, $this->wid);
                $wentWwell =$this->soapCrawl($smartWeb);  
                break;
            default: // Others
                if(VUBLA_DEBUG) echo 'others';
                $wentWwell = $this->rawCrawl();
                
                
        }
       
        $db = VPDO::getVdo(DB_PREFIX.$this->wid);
        $count = $db->fetchOne('select count(*) from products_tmp');
        if(VUBLA_DEBUG) echo 'Found '.$count.' products on this shop'.PHP_EOL;
        
        if($count < 1){
            if(!file_get_contents('http://'.$this->host)){
                if(!VUBLA_DEBUG){@ob_end_clean();  }
                 $this->setCurrentlyBeingCrawled(0);
                $this->customer_debug =  self::NO_CONNECTION;
                return $this->customer_debug;
            }
        }
        $optionhandler_result = OptionHandler::correctOptionsSettings($this->wid);
        if($this->productFinder->finish() && $wentWwell){
             if(!$optionhandler_result){
          
                error_log('Stuff did not work '. ob_get_clean());
                $this->setCurrentlyBeingCrawled(0);
                $this->customer_debug=  self::VUBLA_ERROR;
                
                return $this->customer_debug;
            }
            $this->customer_debug = self::SUCCESS; 
            if($this->db_meta->fetchOne('select email_me from crawllist where wid = ?',array($this->wid))){
                if(!VublaMailer::sendCrawledEmail($this->wid)){
                   if(VUBLA_DEBUG) echo 'Could not sent email' . VublaMailer::$lastError;
                } else {
                    $this->db_meta->exec('update crawllist set email_me = 0 where wid = '.$this->wid);  
                }
            }
            $this->db_meta->exec('update crawllist set last_crawled = '.time().' where wid = '.$this->wid);   
        } else {
             $count = 0; // Make sure count is zero when it fails
             if(is_null($this->customer_debug)){
                $this->customer_debug = self::NO_CONNECTION;
             } 
        }
        
        $this->db_meta->exec('insert into crawl_log (wid, time, products) value ('.$this->wid.','.time().','.$count.' )'); 
        $this->setCurrentlyBeingCrawled(0);
   
        if(!VUBLA_DEBUG){
            @ob_end_clean();
        }
        return $this->customer_debug;
       
    }
        
    protected function setCurrentlyBeingCrawled($bool){
           $this->db_meta->exec('update crawllist set currentlybeingcrawled = '.$bool.' where wid = '.$this->wid);   
    }

    /**
     * Fetches the raw xml from the webshop
     * Tested!
     * Auth not tested in unit test!
     *
    protected function getRaw($wholehost){
       if(Settings::get('http_username', $this->wid)){
           $username = Settings::get('http_username', $this->wid);
           $password = Settings::get('http_password', $this->wid);
           $context = stream_context_create(array(
                'http' => array(
                    'header'  => "Authorization: Basic " . base64_encode("$username:$password")
                )
            ));
            @$raw = file_get_contents('http://'.$wholehost, false, $context);
        } else {
            
           @$raw = file_get_contents('http://'.$wholehost);
        }
        return $raw;
    }

    protected function rawCrawl(){
        
	    $wholehost = $this->host . Settings::get('xml_output_location',$this->wid);
        
        if(VUBLA_DEBUG) echo 'http://'.$wholehost. '<br/>'.PHP_EOL;
	    if(VUBLA_DEBUG) echo Settings::get('http_username', $this->wid) . '<br />';
        
	    $raw = $this->getRaw($wholehost);
      
        
        if(!$raw){
            VublaMailer::sendOnargiEmail('Unable to crawl', 'Using a general method It failed for host '. $this->host .' with wid '. $this->wid .'<br /><br /><pre>'.ob_get_clean().'<br /><br />'.print_r($raw,true).'</pre>');
            return;
        }
        $raw = str_replace('&nbsp;', '', $raw);
        $raw = str_replace('&quot;', '', $raw);
        $catalog = $this->parseXML($raw); 
        $raw = null;
        
        $this->processCatalog($catalog);
        return true;     
        
            
   } 
   
   /** 
    * Handles stuff with the catalog
    * Tested!
    *
   protected function  processCatalog($catalog)
   {   
        $count = 0;

        if(isset($catalog['category'])){
            Category::saveAll($catalog['category'], $this->wid);     
        }
        
        if(VUBLA_DEBUG) { echo 'We found initially '. sizeof($catalog['product']).'<br />'; }
        
        foreach($catalog['product'] as $product)
        {
            $product = $this->productFinder->getProduct($product);
            $product->save();
            $count++;
            if($count % 10 == 0 && VUBLA_DEBUG)
            {
                echo '.';
            }            
        }
       
	   return true;
	}
    
    /**
     * Parses xml into an array
     * Tested!
     *
	protected function parseXML($raw){
	    $from_encoding = Settings::get('encode_from', $this->wid);
        if($from_encoding != Settings::get('vubla_encoding')){
           $raw = iconv($from_encoding, Settings::get('vubla_encoding'), $raw);
        }
       
        libxml_use_internal_errors(true);
        $arrObjData = simplexml_load_string(($raw));
    
        foreach (libxml_get_errors() as $error) {
            if(VUBLA_DEBUG) var_dump($error);
        }

        libxml_clear_errors();
        return objectsIntoArray($arrObjData);
	}
    
    /** 
     * Crawl using soap
     * Tested!
     *
    protected function soapCrawl(ISoapCrawl $client){
        if(!$client->getErrorState()) {
            $cats = $client->getCategories();
          
			 if(is_object($cats) && get_class($cats) == 'CategorySet'){
            	 /* Category sets should be used instead *
            	$cats->removeCategories(array('Default Category', 'Root Catalog'));
                $cats->save();   
            }
            $list = $client->fetchProductIds();
            if(VUBLA_DEBUG) { echo 'We found initially '. sizeof($list)."\n"; }
            
            $count = 0;
            
            foreach($list as $product_id){
            
          
                $xml = $client->getXML($product_id);
                if(is_null($xml)){ continue; }
                if($product = $this->productFinder->getProduct($xml)){
                    $product->save();   
                    $count++;

                    if($count % 10 == 0){
                    
                        if(VUBLA_DEBUG) echo '.';
                    }
                }
            }
        } 
        return true;
    }
    
    function crawl_instant(){
 
        if($this->db_meta->fetchOne('select currentlybeingcrawled from crawllist where wid = '.$this->wid)){
           return Crawler::ALREADY_CRAWLING;
        }
        return $this->crawl();  
    }
       
    /**
     * 
     *
     *
    static function cron($crawl_interval = self::CRAWL_INTERVAL, $max_time_to_be_crawled = self::MAX_TIME_TO_BE_CRAWLED, $wid = 0){
        $db_meta =  VPDO::getVdo(DB_METADATA);
        echo $crawl_interval .' '. $max_time_to_be_crawled;
        if($wid == 0){
            $stm = $db_meta->query('select * from crawllist');
        } else {
            $stm = $db_meta->query('select * from crawllist where wid = '.(int) $wid); 
        }
        
        if(is_null($stm) || $stm === false){
            echo $log = 'Turned Bad!!'."\n";
            var_dump($db_meta);
            exit;
        }
        while (($obj = $stm->fetchObject()) != false){
           echo 'Trying to crawl ' . $obj->wid . "\n";
           
            // If being crawled
            if($obj->currentlybeingcrawled == 1){  
                echo $var = "\t But its being crawled \n";
                // If it is long time it has been crawled in we assume something is wrong and start over.
                if($obj->last_crawled > (time() - $max_time_to_be_crawled) || $obj->last_crawled < 1){
                    continue;
                }
                echo  "\t Screw that im crawling it anyway! \n";
                
                
            }  
            $hasbeencrawled = $db_meta->fetchOne('select products from crawl_log where wid = ? order by time desc limit 1', array($obj->wid)) > 0;
            if($obj->last_crawled < (time() - $crawl_interval) ||  $hasbeencrawled === false){
                echo $var = "\t Crawling it! ";
                $crawler = new Crawler($obj->wid);
                $crawler->crawl();
                $crawler = null;
                echo "\t Done crawling! \n\n\n";
            } else {
                echo  "\t But its been crawled resently \n\n\n";
            }
        }
      
       

        echo "\t Finished All \n\n";
        $db_meta = null;
    }

}
*/