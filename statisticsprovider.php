<?php 

class StatisticsProvider {
    protected $mdb;
    
    function __construct (){
        $this->mdb = vpdo::getVdo(DB_METADATA);
        
    }
    
}