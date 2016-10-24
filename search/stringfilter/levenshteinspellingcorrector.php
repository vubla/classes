<?php

class LevenshteinSpellingCorrector extends SpellingCorrector
{
   
    /**
     * @param $word Word
     * @return sql that gives coorrection of the word
     */
    protected function _correctWord($word)
    {
        $out = array();
        
        ########## GETTING CLOSEST SIMILAR USING LEVENSTEIN
        $max = new stdclass();
        $max->words = array();
        $max->count = PHP_INT_MAX;
                    
        $sql = 'SELECT word,rank FROM words where word not in (select word from '. DB_METADATA .'.dontcarewords)'; // WHERE word like \'' . substr($word->short, 0, 1) . '%\'';
        $result = $this->vdo->prepare($sql);
        $result->execute();
        
        // Get closest similar
        while($row = $result->fetch(PDO::FETCH_NUM))
        {
            $word_in_search = new stdClass();
            $word_in_search->word = $row[0];
            $word_in_search->rank = $row[1];
            $length = levenshtein($word->short, $word_in_search->word);
            if($length > 0) { /// Means the same word. We don't wan't that.
                if(mb_strlen($word_in_search->word,'UTF-8') == mb_strlen($word->short,'UTF-8')) {
                    $length--;
                }
                if($max->count > $length) {
                    $max->words = array();
                    $max->count = $length;
                }
                if ($max->count == $length) {
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
                $word = new SearchWord($w->word);
                $word->rank = $w->rank;
                $word->removeEnds();
               
                $out[] = $word;                  
                
            } 
        }
        
        return $out;
    }

   

}
