<?php
class WebshopStatisticsProvider extends StatisticsProvider {
    private $wid;
    private $wdb;
   
    
    function __construct ($wid){
        parent::__construct();
        $this->wid = $wid;
        $this->wdb = vpdo::getVdo(DB_PREFIX.(int)$wid);
    }
    
    function getNumberOfSearches($ts = 0, $te = null){
        if(is_null($te)) $te = time();
        return $this->wdb->fetchOne('SELECT count(*) from search_log WHERE time >= ? and time <= ?',array($ts,$te));
    }
    
    function getLastCrawled(){
        return $this->mdb->fetchOne('select last_crawled from crawllist where wid = ? ', array($this->wid));
    }
    
    function getProductsCount(){
        return $this->wdb->fetchOne('SELECT count(*) from products');
    }
    
    function getSearchMisses($ts = 0, $te = null) {
        if(is_null($te)) $te = time();
        return $this->wdb->fetchOne("SELECT COUNT(*) FROM search_log AS log WHERE log.q <>  '' AND log.prodids = '' AND time >= ? and time <= ?",array($ts,$te));
    }
    
    function getSearchHits($ts = 0, $te = null) {
        if(is_null($te)) $te = time();
        return $this->wdb->fetchOne("SELECT COUNT(*) FROM search_log AS log WHERE log.q <>  '' AND log.prodids <> '' AND time >= ? and time <= ?",array($ts,$te));
    }
    function getSearchNotHits($ts = 0, $te = null) {
        if(is_null($te)) $te = time();
        return $this->wdb->fetchOne("SELECT COUNT(*) FROM search_log AS log WHERE log.q <>  '' AND log.prodids = '' AND time >= ? and time <= ?",array($ts,$te));
    }
       
    function getSearchWords($maxwords = null,$ts = 0, $te = null, $startRow = null){
        if(is_null($maxwords)){
            $limit = ''; 
        } else {
            if(is_null($startRow)) {
                $limit = "LIMIT ".(int)$maxwords;
            } else {
                $limit = "LIMIT ".(int)$startRow.', '.(int)$maxwords;
            }
        }
        if(is_null($te)) 
        {
            $timeClause = '';
        }
        else 
        {
	        $timeClause = ' AND log.time >= ? AND log.time <= ? ';
        }
        
        $sql =  "SELECT word, COUNT( * ) AS count ".
                "FROM search_words sw ".
                "WHERE word <>  '' ".
                $timeClause .
                "GROUP BY word ".
                "ORDER BY count DESC ".
                $limit;
        //var_dump($sql);
        return  $this->wdb->getTableList($sql,null,array($ts,$te));
    }
    
    function getNotFoundSearches($maxwords = null,$ts = 0, $te = null, $startRow = null){
        if(is_null($maxwords)){
            $limit = ''; 
        } else {
            if(is_null($startRow)) {
                $limit = "LIMIT ".(int)$maxwords;
            } else {
                $limit = "LIMIT ".(int)$startRow.', '.(int)$maxwords;
            }
        }    
        if(is_null($te)) $te = time();
        
        $timeClause = ' AND log.time >= ? AND log.time <= ? ';
        
        $sql =  "SELECT q, COUNT( * ) AS count ".
                "FROM search_log AS log ".
                "WHERE log.q <>  '' AND ".
                        "log.prodids = '' ".
                $timeClause .
                "GROUP BY q ".
                "ORDER BY count DESC ".
                $limit;
        //var_dump($sql);
       return  $this->wdb->getTableList($sql,null,array($ts,$te)); 
     
        
    }
    
    function getSearchLog($ts = 0, $te = null, $maxRows = null, $startRow = null, $fields = '*'){
        if(is_null($te)) $te = time();
        if(is_null($maxRows)){
            $limit = ''; 
        } else {
            if(is_null($startRow)) {
                $limit = "LIMIT ".(int)$maxRows;
            } else {
                $limit = "LIMIT ".(int)$startRow.' , '.(int)$maxRows;
            }
        } 
        
        $join = '';
        
        if($fields != '*') {
            $explodedFields = explode(',', $fields);
            $fields = '';
            foreach ($explodedFields as $field) {
            	$field = trim($field);
                if(!$this->isLegalField($field)) {
                    return array('error' => 'Illegal field: "' . $field . '"');
                }
                if($field == 'words') {
                    $fields .= 'GROUP_CONCAT(words.word) AS words,';
                    $join = 'INNER JOIN search_words words ON log.id = words.log_id ';
                } elseif($field == 'id') {
                    $fields .= 'log.id' . ',';
                } elseif($field == 'time') {
                    $fields .= "`time`" . ',';
                } else {
                    $fields .= $field . ',';
                }
            }
        } else {
            $fields = '';
            foreach (self::$legalFields as $field) {
                if($field == 'words') {
                    $fields .= 'GROUP_CONCAT(words.word) AS words,';
                    $join = 'INNER JOIN search_words words ON log.id = words.log_id ';
                } elseif($field == 'id') {
                    $fields .= 'log.id' . ',';
                } elseif($field == 'time') {
                    $fields .= "`time`" . ',';
                } else {
                    $fields .= $field . ',';
                }
            }
        }
        $fields = substr($fields, 0,strlen($fields)-1);
        
       
        $sql = 'SELECT '.$fields.
               ' FROM search_log log '.$join.
               ' WHERE `time` >= ? and `time` <= ?'.
               ' GROUP by log.id'.
               ' ORDER by log.id DESC '.
               $limit;
        //var_dump($sql);
        return $this->wdb->getTableList($sql,null, array($ts,$te));
        
    }
       
    function getNumberOfSearchesPerNumberOfKeywords($ts = 0, $te = null){

        if(is_null($te)) $te = time();
        
        $timeClause = ' search_log.time >= ' . $this->wdb->quote($ts) . ' AND search_log.time <= ' . $this->wdb->quote($te) . ' ';
        
        $sql =  "
                
                SELECT 0 as number_of_keywords, 
                        count(*) as count, 
                        count(*)*100/(SELECT count(*) from search_log) as percent 
                FROM  
                ( 
                    SELECT search_log.id, count(*) as count 
                    FROM `search_log` 
                    WHERE {$timeClause} and id not in (
                        select log_id as id from search_words)
                    group by search_log.id 
                    having 1
                ) as darp 
                group by darp.count asc 
                
                UNION
                
                SELECT count as number_of_keywords, 
                        count(*) as count, 
                        count(*)*100/(SELECT count(*) from search_log) as percent 
                FROM  
                ( 
                    SELECT search_log.id, count(*) as count 
                    FROM `search_log` 
                        join search_words on search_log.id = log_id 
                    WHERE {$timeClause} 
                    group by search_log.id 
                    having 1
                ) as derp 
                group by derp.count asc ";
        return  $this->wdb->getTableList($sql);
    }
    
    function getNumberOfCrawls($mode) 
    {
        return $this->mdb->fetchOne("SELECT COUNT(*) FROM crawl_log WHERE wid = ? and mode = ?",array($this->wid,$mode));
    }
    
    function getNumberOfRankedWords() 
    {
        return $this->wdb->fetchOne("SELECT COUNT(*) FROM words WHERE rank > 0");
    }
    
    function getNumberOfWords() 
    {
        return $this->wdb->fetchOne("SELECT COUNT(*) FROM words");
    }
    
    function getNumberOfWordsNotDontCare() 
    {
        return $this->wdb->fetchOne("SELECT COUNT(*) FROM words WHERE word NOT IN (SELECT word FROM ".DB_METADATA.".dontcarewords)");
    }
    
	//This is NOT a good way to do it. Instead it should depend directly upon the database
    static private $legalFields = array('id','time','q','search_for','did_you_mean','what_you_mean','prodids','prodnames','ip','useragent');
	
    private function isLegalField($field) {
        foreach (self::$legalFields as $legalField) {
            if($legalField == $field) {
                return true;
            }
        }
        return false;
    }
}



