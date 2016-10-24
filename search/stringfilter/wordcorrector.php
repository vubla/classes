<?php

abstract class WordCorrector extends AnySearchObject
{
    private $asSql = false;
    public function __construct($wid,$asSql = null)
    {
        parent::__construct($wid);
        if(isset($asSql)) $this->asSql = $asSql;
    }
    
    /**
     * @param $word Word
     * @return sql that gives coorrection of the word
     */
    abstract protected function _correctWord($word);
    
    /**
     * Finds a number of words that are close to the words input, but which are actually in the 
     * database for the given webshop, the words input will not be output.
     *
     * @param $words array of Word | Word
     * @return array of array of Words | array of sql containing set of words
     */
    public function correct($words)
    {
        $result_words = array();
        if(is_array($words))
        {
            foreach ($words as $word) 
            {
                if(is_string($word ))
                    $result_words[$word] = $this->_correctWord(new SearchWord($word));
                else if (is_object($word ))
                    $result_words[$word->word] = $this->_correctWord($word);
                else
                    throw new VublaException('Should have received an array of Words (raw strings allowed), but got: '.print_r($word,true));
            }
        }
       
        else 
        {
            throw new VublaException('Should have received an array of Words (raw strings allowed), but got: '.print_r($word,true));
        }
     
        
        
        return $result_words;
    }
  
   
}
