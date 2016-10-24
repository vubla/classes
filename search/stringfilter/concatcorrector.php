<?php

class ConcatCorrector extends WordCorrector
{
    /**
     * @param $word Word
     * @return sql that gives coorrection of the word
     */
    protected function _correctWord($word)
    {
        
    }
    /**
     * Finds a number of words that are close to the words input, but which are actually in the 
     * database for the given webshop, the words input will not be output.
     *
     * @param $words array of Word | Word
     * @return array of array of Words | array of sql containing set of words
     */
    public function correct($words)
    {
        $sqls = array();
        $stopAt = sizeof($words) - 1; // Is calculated prior to the for loop. Else it will run forever. 
        // Concat the words by the preceding and postceding
        for($i = 0; $i <  $stopAt; $i++){
            $word = $words[$i] . $words[$i + 1];
            //$this->words_i_search_for[] = new SearchWord($word);
            $index = $words[$i];
            if(is_object($index))
            {
                $index = $index->__toString();
            }
            if($i == 0)
            {
                $sqls[$index] = 'SELECT * FROM words WHERE word = '.$this->vdo->quote($word);
            }
            else 
            {
                $word2 = $words[$i-1] . $words[$i];
                $sqls[$index] = 'SELECT * FROM words WHERE word IN ('.$this->vdo->quote($word).','.$this->vdo->quote($word2).')';
            }
        }
        if($stopAt >= 1)
        {
            $index = $words[$stopAt];
            if(is_object($index))
            {
                $index = $index->__toString();
            }
            $sqls[$index] = 'SELECT * FROM words WHERE word = '.$this->vdo->quote($words[$stopAt-1].$words[$stopAt]);
        }

        $results = array();
        foreach ($sqls as $word => $sql) 
        {
            $results[$word] = $this->vdo->getTableList($sql);
            if(is_null($results[$word])) $results[$word] = array();
            foreach ($results[$word] as $key => $value) {
                $results[$word][$key] = new SearchWord($value);
                $results[$word][$key]->removeEnds();
            }
        }
        return $results;
    }
}
