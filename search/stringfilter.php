<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}

class StringFilter extends  Basefilter {
    // The original search Query
    protected $original = null;
    public function get_original() 
    {
        return $this->original;
    }
    
     /**
     * The original search is placed here.
     */ 
    protected $original_search_array = array();
    public function get_original_search_array() 
    {
        return $this->original_search_array;
    }
    
    /**
     * What i search for and nothing less.
     * I do as well search for the original queries and some subsets of the original query.
     * Array of SearchWords
     * I is Vubla
     */
    protected $words_i_search_for = array();
    public function get_words_i_search_for() 
    {
        return $this->words_i_search_for;
    }
    
    /**
     * All words spelled wrong are corrected
     * Array of strings
     */
    protected $words_spelling_corrected = array();
    public function get_words_spelling_corrected () 
    {
        return $this->words_spelling_corrected ;
    }
    
    
    /**
     * All words that are corrected to synonyms
     * Array of Strings
     */
    protected $words_synonyms_corrected = array();
    public function get_words_synonyms_corrected  () 
    {
        return $this->words_synonyms_corrected  ;
    }
    
    
    /** 
     * A combined array consisting of spelling correction and synononyms
     */
    protected $did_you_mean = array();
    public function get_did_you_mean() 
    {
        return $this->did_you_mean;
    }
    
    
    /**
     * An array of errors.
     */
    public $errors = array();
    
    
    /**
     * The results in an array
     */
    public $result;
    
    /**
     * Should be merged to a settings 
     */
    public $product_threshold = 10;
    
    function __construct($wid, $search,$fullSearch = null){
        parent::__construct($wid, $fullSearch);
        $this->original = trim($search);
        
        $min_searches = ((int)settings::get('min_search_results', $this->wid));
        if($min_searches)
        {
            $this->product_threshold = $min_searches-1;
        }
    }
    
     /**
     * 
     * Forged in the fires of mount doom:
     *      One function to rule them all, one function to find them
     *      One function to bring them all and in the darkness bind them
     * 
     * Disclaimer!
     * The following function is the most complicated and least well understood of any function within this entire corporation
     * It has been written and rewritten multiple times by several people
     * I take no responsibility or what so ever of any events that may injure your physical or psychological health 
     * while reading the source code of this function, which is also know as the secret ingredient. 
     * 
     * Nah.. Its fine
     * 
     */
    protected function filter(array $product_ids){ 
        if(is_null($this->original)){
            return array();
        }
     
        /// If full search we search amoung everything, which here is equivalent to null.
        $searchAmong = $this->getFullSearch() ? null : $product_ids; 
       
       
        /// If empty query we just search right away.
        if($this->original == '')
        {
            return $this->result = $this->search(array(),$searchAmong);
        }
        
        /// Takes the words remove the ends and put them in the words to process array
        $this->original_search_array = $this->prepareWords($this->original);
        
   
        /// Initial search
        $this->result = $this->search($this->original_search_array,$searchAmong);
        $sizeofOriginal = sizeof($this->result);
        if($sizeofOriginal > $this->product_threshold)
        {
            return $this->result;
        }
        /// If we are over the threshold it will be returned, otherwize we try find corrected words. 
        

        $newWords = array();
        foreach( $this->original_search_array as $word)
        {
             $correctedWords = $this->expandTowardsSpellingMistakes($word);
             $correctedWords[] = $word;
             $bestWord = $this->getBestWord($correctedWords);
             if(is_null($bestWord))
             {
                $bestWord = $word;
             }
             if($bestWord->short != $word->short)
             {
                $newWords[] = $bestWord;
             }
         
             $this->words_spelling_corrected[] = $bestWord;
        }
		
		if($this->original_search_array != $this->words_spelling_corrected)
        {
            $this->did_you_mean[] = $this->words_spelling_corrected;
         }
        
        /// If the original is zero we try search for the corrected words 
        /// Otherwise they are just presented for the user.
        if($sizeofOriginal == 0)
        {
            if(!empty($newWords))
            {
                $this->result = $this->search($newWords,$searchAmong);
            }
        
            $sizeofSpellCorrectedSearch = sizeof($this->result);
            if($sizeofSpellCorrectedSearch > $this->product_threshold)
            {
                return $this->result;
            }
            
            /// If we are here, there has not been found enough products 
            /// So we try to search for synonyms
            
            $wordsToFindSynonymsAmoungst = array_merge($this->original_search_array, $newWords);
            $newWordsSyn = array();
            foreach($wordsToFindSynonymsAmoungst as $word)
            {
                $correctedWords = $this->expandTowardsSynonyms($word);
                $correctedWords[] = $word;
                $bestWord = $this->getBestWord($correctedWords);
                if(is_null($bestWord))
                {
                    $bestWord = $word;
                }
                if($bestWord->short != $word->short)
                {
                    
                    $newWordsSyn[] = $bestWord;
                }
               
                $this->words_synonyms_corrected[] = $bestWord; 
                
            }
            if($wordsToFindSynonymsAmoungst != $this->words_synonyms_corrected)
            {
                $this->did_you_mean[] = $this->words_synonyms_corrected;
            } 
            
            if($sizeofSpellCorrectedSearch == 0)
            {
                if(!empty($newWordsSyn))
                {
                   return $this->result = $this->search($newWordsSyn,$searchAmong);
                }
            }
        }
        
        
        return $this->result;

    }

  
    /**
     * @words Array of strings to search for
     *      if empty array is inserted, everything is found
     *      if non-array is inserted, nothing is found
     * @minOptions Array of strings which names the options to search for
     */
    protected function search($words,$product_ids = null) {
        ################## BUILDING RANKING QUERY ##################
        ################## BUILDING RANKING QUERY ##################
        ################## BUILDING RANKING QUERY ##################
        ################## BUILDING RANKING QUERY ################## 

        $qs = array();
        
        $productIdsWhere = '';
        if(is_array($product_ids))
        {
            if(empty($product_ids)) // No point in searching among nothing
            {
                return array();
            }
            $productIdsWhere = ' and ' . $this->generateWhereClauseFromProductIds($product_ids,'p','id');
        }
        if(!is_array($words)) // If some one puts in a string or object or something crazy, we just return empty array
        {
            return array();
        }
        $searchWords = array();
        if(!empty($words)) // means that every product should be found
        {
          
            foreach($words as $word) {
                if($this->isDontCare($word))
                {
                    $word->multiplyer = 0.5;
              
                }
                $searchWords[] = $this->vdo->quote($word->short);
                $this->words_i_search_for[] =  $word ;
            }   
        }
       # Le grande Finale
        
        $this->query = 
            'SELECT product_id FROM
             (SELECT
                product_id as product_id, MAX(boosted) as boosted, SUM(inname) as inname, SUM(indesc) as indesc, SUM(incategory) as incategory '. 
             ' FROM products p
                    inner join word_relation wr 
                        on wr.product_id = p.id 
                    inner join words w 
                        on w.id = wr.word_id WHERE'; 
       if(!empty($searchWords)){
             $this->query .= '  w.word in ('.implode($searchWords, ',') .' ) ';
       }  else {
              $this->query .= ' 1 = 1 ';
       
       }               
              
                
                
       $this->query .=  $productIdsWhere . ' GROUP BY p.id ) as innerMost
            GROUP BY product_id ORDER BY boosted desc, inname desc, incategory desc, indesc desc ';
    
        ############# STOP BUILDING RANKING QUERY ###############
        ############# STOP BUILDING RANKING QUERY ###############
        ############# STOP BUILDING RANKING QUERY ###############
        ############# STOP BUILDING RANKING QUERY ###############
        ############# STOP BUILDING RANKING QUERY ###############
       
        
        ### Makes sure DB hasen't changed 
        $this->vdo->exec('USE '.DB_PREFIX.(int)$this->getWid());
		if(defined('VUBLA_DEBUG') && VUBLA_DEBUG)
		{
        	//echo ($this->query);
        }
        ### The actual execution
       
        $result = $this->vdo->getTableList($this->query, '');
        if(is_null($result)) {
            return array();
        }
		$out = array();
		foreach ($result as $item) {
			$out[] = (int)$item->product_id;
		}

        return $out;
    }

    
    protected function getBestWord($words) {
        if(!is_array($words) || sizeof($words) == 0) {
            return null;
        }
        $qs = array();
        
        $wheres = array();
        foreach($words as $word) {
            $wheres[] = ' w.word = '.$this->vdo->quote($word->short) .' ';
        }  
        $q = 
            '(SELECT word, COUNT(*) AS num
            FROM 
                products p
                inner join word_relation wr 
                    on wr.product_id = p.id 
                inner join words w 
                    on w.id = wr.word_id
            WHERE 
                ' .implode(' or ', $wheres). ' 
            GROUP BY w.word
            ORDER BY num DESC
            LIMIT 1) AS abcd';  
        
        $query = 'SELECT word FROM ('.$q.') WHERE num > 0';
        
        $temp = $this->vdo->fetchOne($query);
        if(is_null($temp))
        {
            return null;
        }
        $result = new SearchWord($temp);
        $result->removeEnds();
        return $result;
    }

    protected function expandTowardsSynonyms($word) {
            
        $result = array();
        $syns = Dictionary::getThesaurus($word);
        $sql = 'SELECT id FROM `words` WHERE word = ?';
        
		foreach ($syns as $syn) {
		    $syn->removeEnds();
            $temp = $this->vdo->fetchOne($sql,array($syn->short));
            if(!is_null($temp))
            {
                if(!$this->isDontCare($syn)) {
                    //$syn->multiplyer = '0.5';
                
                    $result[] = $syn;
                }
            }
		}
		
		return $result;
    }
    
    protected function expandTowardsSpellingMistakes($word){

        
        $out = array();
        
        ########## GETTING CLOSEST SIMILAR USING LEVENSTEIN
        $max = new stdclass();
        $max->words = array();
        $max->count = PHP_INT_MAX;
                    
        $sql = 'SELECT word FROM words'; // WHERE word like \'' . substr($word->short, 0, 1) . '%\'';
        $result = $this->vdo->prepare($sql);
        $result->execute();
        
        // Get closest similar
        while($row = $result->fetch(PDO::FETCH_NUM))
		{
            $word_in_search = $row[0];
            $length = levenshtein($word->short, $word_in_search);
            if($length > 0) { /// Means the same word. We don't wan't that.
            	if(mb_strlen($word_in_search,'UTF-8') == mb_strlen($word->short,'UTF-8')) {
                    $length--;
                }
                if($max->count > $length) {
                    $max->words = array();
                    $max->count = $length;
                    $max->words[] = $word_in_search;
                } else if ($max->count == $length){
                    $max->words[] = $word_in_search;
                }
            }
            
        }
        $result->closeCursor();
        
        // Now contains best alternatives. 
        if($max->count/(strlen($word) == 0 ? 1 : strlen($word)) >= (double)Settings::get('min_proximity_ratio',$this->getWid())) {
            //return array();
        } else {
            foreach ($max->words as $w) {
                $word = new SearchWord($w);
                $word->removeEnds();
                if(!$this->isDontCare($word)) {
                    //$word->multiplyer = '0.5';
                
                    $out[] = $word;                  
                }
            } 
        }
        
        return $out;
    }

    function get_related_searches()
    {
        return array();   
    }
    
    private function getDisplayIdentifiers($tableAlias = null) {
        $q =    'SELECT name
                 FROM result_display_identifiers';
                 
                 
        $meta = VPDO::getVDO(DB_METADATA); 
        $list = $meta->getTableList($q);
        $result = array();
        foreach ($list as $entry) {
            if($tableAlias == null) {
                $result[] = $entry->name;
            } else {
                $result[] = "$tableAlias." . $entry->name;
            }
        }
        return $result;
    }  
}