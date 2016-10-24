<?php


 class FullScrapeMode extends ScrapeMode {
    
    
     function getPostfix(){
         return '_tmp';
     }
    
    function __toString(){
        return 'full';
    }
}
