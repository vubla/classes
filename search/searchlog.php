<?php
class SearchLog 
{     
    var $q;                               //The user entered search string
    var $time;                            //Time of search
    var $search_for = 'Empty';            //Words to search for
    var $did_you_mean = '-';  //These are the synonyms that were searched for
    var $what_you_mean = '-'; //These are the spelling corrected words
    var $prodids = array();               //Found product ids
    var $prodnames = array();             //Found products
    var $ip = 'Unknown';                  //IP adress of searcher
    var $useragent = 'Unknown';           //Useragent of searcher
    var $option_filter_time = 0;
    var $sorting_time = 0;
    var $widget_factory_time = 0;
    var $string_filter_timer = 0;
    var $total_search_time = 0;
    var $product_factory_time = 0;
    var $related_searches = '-';
    function __construct($wid, $searchResult)
    {
      
       // parent::__construct($wid);
        $this->option_filter_time = $searchResult->option_filter_time * 1000000;
        $this->sorting_time = $searchResult->sorting_time *1000000;
        $this->widget_factory_time = $searchResult->widget_factory_time *1000000;
        $this->string_filter_timer  = $searchResult->string_filter_timer *1000000;
        $this->product_factory_time = $searchResult->product_factory_time*1000000;
        $this->total_search_time  = $searchResult->total_search_time*1000000;
        $this->time = time(); 
        $this->q = $searchResult->original;
        $this->search_for = $searchResult->searchwords;
        
        if(!empty($searchResult->synonyms_corrected_to))
        {
           $temp = array();
           foreach ($searchResult->synonyms_corrected_to as $syn) {
               $temp[] = $syn->word; 
           }
           $this->did_you_mean = implode(', ', $temp);
        }
        if(!empty($searchResult->spelling_corrected_to))
        {
           $temp = array();
           foreach ($searchResult->spelling_corrected_to as $spell) {
               $temp[] = $spell->word; 
           }
           $this->what_you_mean = implode(', ', $temp);
        }
        foreach ($searchResult->products as $product) {
            $this->prodids[] = $product->pid;
            if(!empty($product->name))
            {
                $this->prodnames[] = $product->name;
            }
        }
        if(!empty($this->related_searches))
        {
            $temp = array();
            foreach ($searchResult->related_searches as $searches) {
                
               
               
                $temp[] = $searches->word . '(' . $searches->products . ')';
                
            }
            $this->related_searches = implode(', ',  $temp);
        }
        @$this->ip = IP;
        @$this->useragent = USERAGENT;
      
    }
    
    /*
    function save($db) {
        $vars = get_object_vars($this);
        $keys = array_keys($vars);
        $this->prodids = implode(', ', $this->prodids);
        $this->prodnames = implode(', ' ,$this->prodnames);
        $keys_str = implode(', ', $keys);
        
        array_walk($keys, create_function('&$a', '$a = ":".$a;')); /// Prepend : to every key
        
        $stm = $db->prepare( 'INSERT INTO search_log ( '.$keys_str.') VALUES ( ' . implode(',', $keys) . ') ;' );
        $stm->execute( get_object_vars($this) );
        
    }*/
    
    function saveNew($db) {
        
        $words = $this->search_for;
        $this->search_for = null;
        $this->prodids = implode(', ', $this->prodids);
        $this->prodnames = implode(', ' ,$this->prodnames);
        $vars = get_object_vars($this);
        $keys = array_keys($vars);
        $keys_str = implode(', ', $keys);
        
        array_walk($keys, create_function('&$a', '$a = ":".$a;')); /// Prepend : to every key
         
        $db->beginTransaction();
        $q = 'INSERT INTO search_log ( '.$keys_str.') VALUES ( ' . implode(',', $keys) . ')';
        
        $stm = $db->prepare( $q );
       //   echo "exi2 ".__LINE__. " ";echo $q; var_dump($vars); exit;
        $stm->execute( $vars );
        $stm->closeCursor();
        
        $id = $db->fetchOne("SELECT MAX(id) FROM search_log");
     
        if(!$id) {
            $db->rollback();
            throw new VublaException('Failed to find id again -- rolling back');
        }
        $db->commit();
        $this->saveWords($db,$id,$words,0);
        $this->updateRank($db);
    }

    function saveWords($db , $searchId , $searchWords, $num = null){
        //Find num for search id
        $sql =  "SELECT MAX(log_num) FROM search_words WHERE ".
                "log_id = " . $db->quote($searchId);
        
        if(!isset($num) || $num < 0) {
            $num = $db->fetchOne($sql);
            if(!$num) {
                $num = 0;
            }
        }
        foreach($searchWords as $word) {
            $num++;
            $sql =  "INSERT INTO search_words (log_id,log_num,multiplyer,word,short,ending) VALUES (".
                    $db->quote($searchId).",".$db->quote($num).",".$db->quote($word->multiplyer).",".
                    $db->quote($word->word).",".$db->quote($word->short).",".$db->quote($word->ending).")";
            
            $temp = $db->exec($sql);
            if($temp != 1) {
                throw new VublaException("Failed to insert on: ".$sql);
            }
        }
    }
    
    function updateRank($db)
    {
        
        $db->prepare("update words set rank = rank +1 where word = ?")->execute(array($this->q));
    }
}
?>
