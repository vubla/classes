<?php

class UpdateScrapeMode extends ScrapeMode {
    
    
     function getPostfix(){
         return '';
     }
     
     function __toString(){
        return 'update';
    }
    
}