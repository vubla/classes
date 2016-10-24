<?php 

checkConfig();


 class XmlScraper extends Scraper {
     protected function getFetcher() {
        return new XmlFetcher($this->wid);
    }
    

}
