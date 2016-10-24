<?php 

checkConfig();


class SmartwebScraper extends SoapScraper {
    
    
    
    protected function getFetcher() {
        return new SmartwebScraper($this->wid);
    }
}
