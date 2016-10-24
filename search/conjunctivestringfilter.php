<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}

class ConjunctiveStringFilter extends  StringFilter  
{
    protected $correctors = array();
    protected $wordsCorrected = array();
    protected $wordSelector;
    private $maxSearches = 21;
    
    function __construct($wid, $search,$fullSearch = null){
        parent::__construct($wid, $search, $fullSearch);
        $this->correctors[] = new ConcatCorrector($this->wid);
        $this->correctors[] = new CharacterSwapperCorrector($this->wid);
        $this->correctors[] = SpellingCorrector::create($this->wid);
        $this->correctors[] = new SynonymCorrector($this->wid);
        $this->wordSelector = new RankingWordSelector($this->wid);
        $maxSearches = settings::getLocal('max_search_tries',$this->wid);
        if(!is_null($maxSearches)) $this->maxSearches = $maxSearches;
    }
    

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
        $sizeOfOriginal = sizeof($this->result);
        if($sizeOfOriginal > $this->product_threshold)
        {
            return $this->result;
        }
        /// If we are over the threshold it will be returned, otherwize we try find corrected words. 
        
        $wordsToCorrect = $this->original_search_array;
        $this->wordsCorrected = $this->original_search_array;
        $sizeOfPrevious = $sizeOfOriginal;
        $i = 0;
        $bestDidYouMean = null;
        $bestCount = 0;
        foreach ($this->correctors as $corrector) 
        {
            $newWords = array();
            $correctedWordsSet = $corrector->correct($wordsToCorrect);
            foreach ($correctedWordsSet as $words) 
            {
                foreach ($words as $word) 
                {
                    $this->wordsCorrected[] = $word;
                    //$wordsToCorrect[] = $word;
                    $newWords[] = $word;
                }
            }
            $this->setCorrectionVariable($newWords,$i);
            
            $wordSetsToSearch = $this->wordSelector->selectWordArrays($correctedWordsSet,$this->maxSearchesPrCorector());
            
            foreach ($wordSetsToSearch as $arrayOfWordsToBeSearchedFor) 
            {
                if(!empty($arrayOfWordsToBeSearchedFor) && 
                    array_intersect($arrayOfWordsToBeSearchedFor, $this->original_search_array) != $arrayOfWordsToBeSearchedFor)
                {
                    $tempResult = $this->search($arrayOfWordsToBeSearchedFor,$searchAmong);
                    if(sizeof($tempResult) > 0 && sizeof($tempResult) > sizeof($this->result))
                    {
                        $this->did_you_mean[] = $arrayOfWordsToBeSearchedFor;
                
                        /// If the original is zero we try search for the corrected words 
                        /// Otherwise they are just presented for the user.
                        if(sizeof($this->result) == 0)
                        {
                            if(sizeof($tempResult) > $this->product_threshold)
                            {
                                return $this->result = $tempResult;
                            }
                        }
                    }
                    else if(sizeof($tempResult) > $bestCount)
                    {
                        $bestCount = sizeof($tempResult);
                        $bestDidYouMean = $arrayOfWordsToBeSearchedFor;
                    }
                }
            }
            $i++;
        }
        if(sizeof($this->result) == 0 && sizeof($this->original_search_array) > 1)
        {
            $this->result = $this->search($this->words_i_search_for,$searchAmong,sizeof($this->original_search_array)-1);
        }
        if($bestDidYouMean != null && empty($this->did_you_mean) && $bestCount > sizeof($this->result))
        {
            $this->did_you_mean[] = $bestDidYouMean;
        }
        
        
        return $this->result;

    }

    protected function setCorrectionVariable($array, $correctorNumber)
    {
        switch ($correctorNumber) {
            case 2:
                $this->words_spelling_corrected = $array;
                break;
            case 3:
                $this->words_synonyms_corrected = $array;
                break;
            default:
                
                break;
        }
    }

  
    /**
     * @param words Array of strings to search for
     *      if empty array is inserted, everything is found
     *      if non-array is inserted, nothing is found
     * @param minOptions Array of strings which names the options to search for
     */
    protected function search($words,$product_ids = null, $fuzzyThres = 0) 
    {
        $productIdsWhere = '';
        if(is_array($product_ids))
        {
            if(empty($product_ids)) // No point in searching among nothing
            {
                return array();
            }
            $productIdsWhere = ' and ' . $this->generateWhereClauseFromProductIds($product_ids,'wr','product_id');
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
                $this->words_i_search_for[] =  $word;
            }   
        }
        
        $temptable = 
            'SELECT
                product_id as product_id, SUM(inname) as inname, SUM(indesc) as indesc, SUM(incategory) as incategory, count(w.word) as wordcount '. 
             ' FROM  word_relation wr 
                        
                    inner join words w 
                        on w.id = wr.word_id WHERE'; 
       if(!empty($searchWords)){
             $temptable .= '  w.word in ('.implode($searchWords, ',') .' ) ';
       }  else {
              $temptable .= ' 1 = 1 ';
       }
       $temptable .=  $productIdsWhere . ' GROUP BY wr.product_id';
       
       $whereClause = 'wordcount >= ';
       if($fuzzyThres > 0)
       {
           $whereClause .= $this->vdo->quote($fuzzyThres);
       }
       else
       {
           $whereClause .= $this->vdo->quote(sizeof($searchWords));
       }
       $this->query = 'SELECT product_id 
            FROM ('.$temptable.') AS thefromtable
            WHERE '.$whereClause.'
            ORDER BY inname desc, incategory desc, indesc desc ';
      
        ### Makes sure DB hasen't changed 
        $this->vdo->exec('USE '.DB_PREFIX.(int)$this->getWid());
        if(defined('VUBLA_DEBUG') && VUBLA_DEBUG)
        {
            //
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

    public function getCorrectedWords()
    {
        return $this->wordsCorrected;
    }
    
    protected function maxSearchesPrCorector()
    {
        if(empty($this->correctors))
        {
            return 0;
        }
        return ($this->maxSearches-1)/sizeof($this->correctors);
    }
    
    function get_related_searches()
    {
        
        return SearchFinder::create($this->wid)->addSearchWords($this->getCorrectedWords())->getRelatedSearches();
    }
    
}