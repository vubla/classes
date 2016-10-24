<?php




class SearchFinder extends AnySearchObject {
    
    
    private $_input = array();
    private $_output = array();
    private $_edited = false;
    
   
    
    public static function create($wid)
    {
        return new SearchFinder($wid);
    }
    
    public function addSearchWords($words)
    {
       
        $this->_input = array_merge($this->_input,$words);   
     
        $this->edited = true;
        return $this;
    }
    
    function getSearchWords()
    {
        return $this->_input;
    }
    
    public function getRelatedSearches()
    {
        if(!$this->edited)
        {
            return $this->_output;
        }   
        if(empty($this->_input))
        {
            return array();
        }
       
        $quoted_word_strings = array();
        foreach($this->_input as $word)
        {
            if(is_string($word)){
                $word = new SearchWord($word);
            }
            if(!is_object($word) or !($word instanceof Word))
            {
                continue;
            } 
            $quoted_word_strings[] = $this->vdo->quote($word);          
            
            
        }
        $sql = "select w.*,  count(*) as products  from words w 
                                            inner join word_relation wr 
                                                on w.id = wr.word_id 
                                            where word in (".implode(',', $quoted_word_strings).") 
                                            and rank > 0 group by word_id order by rank desc limit ". settings::get('related_searches_threshold');
                                            
        $this->_output = $this->vdo->getTableList($sql);
        if(!is_array($this->_output))
        {
            return array();
        }
        foreach($this->_output as $key=>$w)
        {
            $this->_output[$key] = new SearchWord($w->word, $w->id);
            $this->_output[$key]->rank = $w->rank;
            $this->_output[$key]->products = $w->products;
        }
        
        $this->_edited = false;
        return $this->_output;
    }
    
}
