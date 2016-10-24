<?php 

checkConfig();


class AnyScrapeObject {
    
    /**
     *
     * @var string 
     */
    protected $hostname;
   
    /**
     *
     * @var int 
     */
    protected $wid;
    
    /**
     *
     * @var vpdo 
     */
    protected $wpdo;
    
    /** 
     *
     * @var vpdo 
     */
    protected $mpdo;
    
    /**
     *
     * @var scrapeexception
     */
    protected $error;

    /**
     * Legacy, must be removed at some point when it can be avoided
     * @var int
     * 
     */
     static $static_wid;
    
    /**
     *
     * @param int $wid 
     */
    function __construct($wid)
    {
        if($wid < 1 )
        {
            VOB::_n("Missing wid");
            throw new VublaException("Missing Wid");
     
        }
        $this->wid = $wid;
        self::$static_wid = $wid;
        $this->mpdo = VPDO::getVdo(DB_METADATA);
        $this->wpdo = VPDO::getVdo(DB_PREFIX.$wid);
        $this->hostname = $this->getHostname();
    }
    
    /**
     *
     * @return string 
     */
    protected function getHostname()
    {
        if(is_null($this->hostname))
        {
            $this->hostname = $this->mpdo->fetchOne('select hostname from webshops where id = ?', array($this->wid));
        }
        if(is_null($this->hostname)){
           throw new ScrapeException("Missing Hostname");
         
        }
        return $this->hostname;
    }
    
    final protected function getWid()
    {
        return $this->wid;
        
    }
    
    protected function setError( ScrapeException $e)
    {
        $this->error = $e;
    }
    
    function getError()
    {
        return $this->error;
    }
    
    protected function verboseOut($str)
    {
        VOB::v_n($str);
    }
}