<?php 

checkConfig();


class MagentoScraper extends SoapScraper {
    
    
    
    function getFetcher() {
        return new MagentoFetcher($this->wid);
    }
    
    
   
}
