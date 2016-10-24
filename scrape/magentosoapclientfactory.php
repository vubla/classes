<?php 



class MagentoSoapClientFactory extends AnyScrapeObject {
    static private $instances = array();
    
    static public function getFactory($wid)
    {
        if(!array_key_exists($wid, self::$instances))
        {
            self::$instances[$wid] = new MagentoSoapClientFactory($wid);
        }
        return self::$instances[$wid];
    }
    
    public function create()
    {
        $key = settings::get('mage_api_key',$this->wid);
        $user = settings::get('http_username',$this->wid);
        $pass = settings::get('http_password',$this->wid);
        $timeout = settings::get('connection_timeout',$this->wid);
        if($timeout == 0) $timeout = NULL;
        $delay = settings::get('scrape_delay', $this->wid);
        return new MagentoSoapClient($this->getHostname(),$key,'vubla',$user,$pass,$timeout, $delay); 
    }
}
    