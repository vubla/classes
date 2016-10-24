<?php 

checkConfig();


class VublaXmlParser  {
    
    private $host; 
    private $context;
    protected $from_encoding = 'UTF-8';
    
    
    
    
    public function __construct($host, $context = null) {
        $this->host = $host;
        $this->context = $context;
    }
    
    public function setFromEncoding($enc){
        $this->from_encoding = $enc;
    } 
    
    
    protected function getRaw($host){
        if(is_null($this->context)){
               @$raw = file_get_contents('http://'.$host);
        } else {
             @$raw = file_get_contents('http://'.$host, false, $this->context);
        }
     
        
        return $raw; 
    }

    public function getCatalog(){
        
        VOB::_n('Fetching catalog from http://'.$this->host .'...');
	
        
        $raw = $this->getRaw($this->host);
      
       
        if(!$raw){
            //VublaMailer::sendOnargiEmail('Unable to crawl', 'Using a general method It failed for host '. $this->host .' <br /><br /><pre>'.ob_get_clean().'<br /><br />'.print_r($raw,true).'</pre>');
            throw new NoScrapeFileException('Unable to crawl. Using an xml method It failed for host '. $this->host .' <br /><br /><pre></pre>');
        }
      
        try {
            $catalog = $this->parseXML($raw); 
        } catch (ScrapeException $e)
        {
            throw new ScrapeException('Unable to crawl. Using an xml method it failed for host '. $this->host .'. Old Message: ' .$e->getMessage(), 0 , $e);
       
        }
        $raw = null;
        VOB::_n('Done fetching catalog');
        return $catalog;     
        
            
   } 
   

    protected function parseXML($raw){
        
        if($this->from_encoding != Settings::get('vubla_encoding'))
        {
            $raw = iconv($this->from_encoding, Settings::get('vubla_encoding'), $raw);
        }
        $this->checkForEncodingErrors($raw);
         libxml_clear_errors();
        $raw = str_replace('&nbsp;', '', $raw);
        $raw = str_replace('&quot;', '', $raw);
        $raw = str_replace("\r\n", "\n", $raw);
        $raw = str_replace("\r", "\n", $raw);
        $raw = str_replace('&', 'og', $raw);
        libxml_use_internal_errors(true);
        $arrObjData = simplexml_load_string(($raw));
        
        $errors = array();
        foreach (libxml_get_errors() as $error) 
        {
            $errors[] = $error;
        }
        
        if(!empty($errors))
        {
            throw new ScrapeException( print_r($error,true));
        }

        libxml_clear_errors();
        return $this->toHonorMed24WithTheirUTF8DatabaseAndISO88591Webserver(
       //return (
       objectsIntoArray($arrObjData));
    }

    protected function toHonorMed24WithTheirUTF8DatabaseAndISO88591Webserver($data)
    {
        if(strpos($this->host, 'med24') === false) return $data;
        
        function innerMed($innerData, $key)
        {
            if(is_array($innerData))
            {
                foreach ($innerData as $key => $value) 
                {
                    $innerData[$key] = innerMed($value, $key);
                }
                return $innerData;
            }
            else if(is_string($innerData) )
            {
               
               // if($key == 'buy_link'){
                    return $innerData = str_replace('ogamp;','&',str_replace('ogamp;amp;','&', $innerData)); //iconv('UTF-8','ISO-8859-1',  )));
                //} else {
                 //   return $innerData; // = iconv('UTF-8','ISO-8859-1', $innerData);
               // }      
            }
            else 
            {
                throw new ScrapeException('Med24 has something illegal, i cannot convert this: '.PHP_EOL . $key);
            }
        }
        
        return innerMed($data, '');
        
    }
    
    protected function checkForEncodingErrors(&$data)
    {
        if(!VOB::getVerbose()) return;
        
        $pos = false;
        $possibleErrors = array('Ã¥','Ã¦','Ã¸');
        foreach ($possibleErrors as $value) 
        {
            $remaining = $data;
            while(!empty($remaining))
            {
                if(($pos = strpos($remaining, $value)) !== false) 
                {
                    VOB::v_n('Possible encoding error around:');
                    VOB::v_n(substr($remaining, $pos-20,40));
                    $remaining = substr($remaining, $pos+1);
                }
                else 
                {
                    break;
                }
            }
        }
    }
    
    
}
