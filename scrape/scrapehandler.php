<?php


if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}

/// ENCODING 
/// The crawler does not care about encoding!!!

/* The Crawler
 * Gets content from webshops and delivers it in XML to the productFinder
 */
class ScrapeHandler {
    
   
  
    /**
     *
     * @var \Scraper 
     */
    private $scraper; 
    
    public $wid;
    private $db_meta;
    private $starttime;
    function __construct($wid){
        $this->wid = $wid;
        $this->db_meta =  VPDO::getVdo(DB_METADATA);
        $this->starttime = time();
    }
    
    
    /**
     *  Returns the scraper 
     * @return \Scraper 
     */
    protected function getScraper(){
        if(is_object($this->scraper)){
            return $this->scraper;
        }
        $webshopType = $this->db_meta->fetchOne('select type from webshops where id = ?',array($this->wid));
        
        switch($webshopType){
            case 1: 
                $scraper = new OscommerceScraper($this->wid);
                break; 
            case 2: // Magento       
                $scraper = new MagentoScraper($this->wid);
                break;    
            case 3: // Presta
                $scraper = new PrestaScraper($this->wid);
                break;
            case 4: // Others
                $scraper = new OscommerceScraper($this->wid);
                break;
            case 5:
                $scraper = new SmartwebScraper($this->wid);
                break;
            default: // Others
                 $scraper = new XmlScraper($this->wid);
                 break;
               
        }
        $this->scraper = $scraper;
        return $scraper;
        
    }

    public function startScraping($forced = false){
        ini_set('max_execution_time',36000 );
        Scraper::$errors = array();
        $db = VPDO::getVdo(DB_PREFIX.$this->wid);
        $initialCount = $db->fetchOne('select count(*) from products');
        if(!$forced)
        {
            vob::_n("accuiring locks"); 
            $this->db_meta->exec("LOCK TABLES crawllist WRITE;");
       
            $being_crawled = $this->db_meta->fetchOne("select currentlybeingcrawled from crawllist where wid = ?", array($this->wid));
            if($being_crawled == 1){
                $this->db_meta->exec("UNLOCK TABLES;");
                return;
            }
        }
        vob::_n("setting setCurrentlyBeingCrawled"); 
        $this->setCurrentlyBeingCrawled(1, 'Started');
        vob::_n("done setCurrentlyBeingCrawled"); 
        if(!$forced)
        {
            vpdo::meta()->exec("UNLOCK TABLES;");
            vob::_n("done unlocking");
        }
        try {
            $scraper = self::getScraper($this->wid);
    
            $scraper->scrape();
        } catch (Exception $e){
             vob::_n("caucht exception");
            $this->setCurrentlyBeingCrawled(0, $e);
            throw $e;
        }
         
        
        $count = $db->fetchOne('select count(*) from products'.ScrapeMode::getPF());
        VOB::_n('Found '.$count.' products on this shop'.PHP_EOL);
        
        if($count < 1){
            $e =  new ScrapeException('There where no products found');
            $this->setCurrentlyBeingCrawled(0, $e);
            throw $e;
           
        }
        
        
        
       
       
        
        
         VOB::_n('Finishing...');
        $optionhandler_result = OptionHandler::correctOptionsSettings($this->wid);
        if($scraper->finish()){
             if(!$optionhandler_result){
          
                $e = new ScrapeException('The optionhandler found an error');
                $this->setCurrentlyBeingCrawled(0, $e);
                throw $e;
                
               
            }

            
              if(vpdo::getVdo(DB_METADATA)->fetchOne('select email_me from crawllist where wid = ?',array($this->wid))){
                if(VublaMailer::sendCrawledEmail($this->wid)){
                    $this->db_meta->exec('update crawllist set email_me = 0 where wid = '.$this->wid);  
                }
            }
            
            // If we got here, everything went well
             if(ScrapeMode::get() == 'update'){
                $eq = 'last_updated = '. $this->starttime;
            } else if (ScrapeMode::get() == 'full'){
                $eq  = 'last_updated = '. $this->starttime.', last_crawled = '. $this->starttime;
            } else {
                $e = new ScrapeException('Terrible error');
                $this->setCurrentlyBeingCrawled(0, $e);
                throw $e;
                
            }
            $this->db_meta->exec('update crawllist set '.$eq.', scrape_asap = 0  where wid = '.(int)$this->wid ); 
         
    // echo 'update crawllist set last_'.$table.' = '.time().', scrape_asap = 0  where wid = '.(int)$this->wid ;
            
            
            
            
        } else {
             $count = 0; // Make sure count is zero when it fails
            
        }
        
        $this->db_meta->exec('insert into crawl_log (wid, time, products, mode, initial_products) value ('.
            $this->wid.','.
            $this->starttime.','.
            $count.' , '.
            $this->db_meta->quote(ScrapeMode::get()).' , '.
            $this->db_meta->quote($initialCount).')'); 
       
        
        $this->setCurrentlyBeingCrawled(0,'Success');
        $finalCount = $db->fetchOne('select count(*) from products');
        $deviation = 0.10;
        if(($initialCount*(1-$deviation) >  $finalCount || $initialCount*(1+$deviation) < $finalCount) && $initialCount > 100 )
        {
            VublaMailer::sendOnargiEmail('Scrape Deviation Fault', "While scraping {$this->wid} we found {$finalCount} and last time we found {$initialCount}", "scrape error".$this->wid);
            if(ScrapeMode::get() == 'update')
            {
                $user = new User();
                $user->crawlAtNextCron($this->wid);
            }
        }
        $entriesInCrawllog = $this->getNumberOfCrawls();
        if($entriesInCrawllog == 1)
        {
            $user = new User();
            $user->crawlAtNextCron($this->wid);  
        }
        VOB::_n('Finished');
        
        return true;
       
    }
        
    protected function setCurrentlyBeingCrawled($bool, $status = 'no respond'){
           if(is_object($status)){
               $status = get_class($status);
           }
           $this->db_meta->exec('update crawllist set currentlybeingcrawled = '.$bool.', status = '.$this->db_meta->quote($status).' where wid = '.$this->wid);   
    }
    
    protected function getNumberOfCrawls()
    {
        $stat = new WebshopStatisticsProvider($this->wid);
        return $stat->getNumberOfCrawls('full');
    }

    /**
     * This checks whether or not it is already scraping
     * @return bool
     * @throws AlreadyScrapingException 
     */
    function startSafeScraping(){
       
        if($this->db_meta->fetchOne('select currentlybeingcrawled from crawllist where wid = '.$this->wid)){
           throw new AlreadyScrapingException();
        } 
     
        return $this->startScraping();  
    }
       

    function outputEnabled($b){
         if($b){
           VOB::setTarget(VOB::TARGET_STDOUT);
        } else {
            VOB::setTarget(VOB::TARGET_NONE);
        }   
        //$this->getScraper()->outputEnabled($b);
    }
    
  
}
