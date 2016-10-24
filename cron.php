<?php


class cron {
    
    private static $wids = array();
    private static $force = false;
    private static $asap = false;
    
    static function runSome(array $wids,$force = false, $asap = false)
    {
        self::$wids = $wids;
        self::$force = $force;
        self::$asap = $asap;
        self::scrape();
        self::fetchLogs();
    }
    
    static function runAll($time = 0)
    {
        self::$wids = array();
        self::scrape($time);
        self::fetchLogs();
    }
    
     /**
     * 
     *
     */
    static function scrape($time = 0, $scrapehandler = 'ScrapeHandler'){
        
        if($time == 0)
        {
            $time = time();
        }
       
        $db_meta =  VPDO::getVdo(DB_METADATA);
        $whereClause = ' 1 ';
        if(!empty(self::$wids))
        {
            $whereClause = ' wid in ('.implode(',', self::$wids).') ';
        }
        $stm = $db_meta->query('select * from crawllist where '.$whereClause.' order by last_crawled asc');
      
        
        if(is_null($stm) || $stm === false){
             $log = 'Turned Bad!!'."\n";
           
            return;
        }
        
        /** 
         * Value for max number of concurrent count.
         * @var int
         */
        $number_of_concurrent_scrapes = Settings::get('number_of_concurrent_scrapes');
        $number_of_concurrent_scrapes =  $number_of_concurrent_scrapes ? $number_of_concurrent_scrapes : 5;
        

        while (($obj = $stm->fetchObject()) != false)
        {

           
            echo 'Trying to crawl ' . $obj->wid . "\n";
         
            /**
             * New Scrape disabled feature, added: 20120613
             * 
             */
            if(Settings::get('scrape_disabled', $obj->wid))
            {
                echo "Not scraping as scrapign is disabled\n";
                continue;
            }  

            if(!self::checkMasterScrapeLimit(new Webshop($obj->wid), $time) )
            {
                echo "There to many in this webshops family that are currently scraping\n";
                continue; 
            }

            /**
             * Ensure Language initialization
             */
            Language::init($obj->wid);


            /** 
             * Ensutes that not to many are being scraped at once. (At 20130613, i have never received that email :P)
             * The check is redundant anyway as we limit using Vubla Scraping Service (but it does not hurt) 
             */
            $current_scrapings = $db_meta->getRowCount('select * from crawllist where currentlybeingcrawled = 1');
            if($current_scrapings >= $number_of_concurrent_scrapes){
                echo "\tToo many are being crawled\n";
                VublaMailer::sendOnargiEmail('To many is being scraped', "There are currently  {$current_scrapings} webshops being scraped and limit is ".$number_of_concurrent_scrapes);
                return;
            }
           
            // If being crawled
            if($obj->currentlybeingcrawled == 1 && !self::$force){  
                echo $var = "\t But its being scraped \n";
                // If it is long time it has been crawled in we assume something is wrong and start over.
                if(($time < ($obj->last_crawled + ($obj->crawl_interval*2)) )      || $obj->last_crawled < 1){ //&& $time < ($obj->last_updated + $obj->update_interval*1.5)
                    
                    continue;
                }
                echo  "\t Screw that im scraping it anyway! \n";
                
                
            }  
          //  $hasbeencrawled = $db_meta->fetchOne('select products from crawl_log where wid = ? order by time desc limit 1', array($obj->wid)) > 0;
            if(self::$asap === true)
            {
                $obj->scrape_asap = 1;
            }
        
            if((self::isItNight($time) && $time >= ($obj->last_crawled + $obj->crawl_interval)) ||  $obj->scrape_asap){
                echo $var = "\t Scraping it! ";
                $handler  = new $scrapehandler($obj->wid);
                $handler->outputEnabled(true);
                scrapemode::set('full');
                try {
                    $handler->startScraping(self::$force);
                } catch(VublaException $e){
                    VOB::_n(print_r($e));
                }
               // $handler = null;
                echo "\t Done Scraping! \n\n\n";
            } 
            else if($time >= ($obj->last_updated + $obj->update_interval) && $obj->update_mode_supported)
            {
                echo $var = "\t Updating it! ";
                $handler  = new $scrapehandler($obj->wid);
                $handler->outputEnabled(true);
                scrapemode::set('update');
                try {
                    $handler->startScraping(self::$force);
                } catch(VublaException $e){
                    VOB::_n(print_r($e));
                }
               // $handler = null;
                echo "\t Done Updating! \n\n\n";
            }
            else {
                
                echo  "\t But its been crawled resently \n\n\n";
            }
        }
      
       

        echo "\t Finished All \n\n";
        $db_meta = null;
    }

    static function isItNight($time = 0){
        if($time == 0)
        {
            $time = time();
        }
        return (date('G', $time) < 7 ||  date('G',$time) > 22);
        
    }
    
    /**
     * Checks if there are not too many webshops in the family that are being scraped.
     * @param  Webshop $w
     * @return boolean
     */
    static function checkMasterScrapeLimit(Webshop $w, $time)
    {
        $scrape_master_limit = settings::get('scrape_master_limit', (int)$w->getWid());
        $scrape_master_night_limit = settings::get('scrape_master_night_limit', (int)$w->getWid());
       
        $family = $w->getFamily();
        if(sizeof($family) == 1)
        {
            return true;
        }
        $family_scrapes = vdo::meta()->fetchOne("select count(*) from crawllist where currentlybeingcrawled = 1 and wid in (" . implode(',', $family). ")");
        if(self::isItNight($time))
        {
            return !$scrape_master_night_limit  || $scrape_master_night_limit > $family_scrapes;
        } else {
            return !$scrape_master_limit  || $scrape_master_limit > $family_scrapes;
        }
       
       
    }
    
    static function fetchLogs()
    {
        VOB::_n('Fetching logs');
        $db_meta =  VPDO::getVdo(DB_METADATA);
        
        $stm = $db_meta->query('select * from webshops w inner join crawl_log l on w.id = l.wid where l.products > 1  and type = 2 group by wid');
        while (($obj = $stm->fetchObject()) != false)
        {
            VOB::_n('Wid:' .$obj->wid);
            if(!settings::get('mage_log_fetched', $obj->wid))
            {
                VOB::_n('Processing');
                $db_wid =  VPDO::getVdo(DB_PREFIX.$obj->wid);
                $log = new MagentoSearchLog($obj->wid);
                $log->fetch()->save();
                settings::setLocal('mage_log_fetched', 1,$obj->wid);
            }
             VOB::_n('Done:' .$obj->wid);
        } 
        VOB::_n('Done fetching logs');
    }
}
