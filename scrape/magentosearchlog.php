<?php



class MagentoSearchLog extends AnyScrapeObject 
{
    private $_log;
    private $client;
    function fetch()
    {
        if(is_null($this->_log))
        {
            $client = $this->getClient();
            $this->_log = $client->call('vubla_search.fetch');
        }
        return $this;
    }
    
   
    function save()
    {
        $db = vpdo::getVdo(DB_PREFIX.$this->wid);
        foreach($this->_log as $key=>$entry)
        {
            if(strlen($entry['query_text']) > 1){
                $stm = $db->prepare('update  words set rank = rank + ? where word = ?');
                $stm->execute(array($entry['popularity'], strtolower($entry['query_text'])));
                $stm->closeCursor();
            }
        }
        return $this;
    }
    
    
     protected function getClient() {
        if(is_null($this->client))
        {
            $this->client = MagentoSoapClientFactory::getFactory($this->wid)->create();
        }
        return $this->client;
    }
    
    
}
