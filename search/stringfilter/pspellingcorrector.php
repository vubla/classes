<?php

class PSpellingCorrector extends SpellingCorrector
{
   
    /**
     * @param $word Word
     * @return sql that gives coorrection of the word
     */
    protected function _correctWord($word)
    {
        
        // Now contains best alternatives. 
        if($max->count/(strlen($word) == 0 ? 1 : strlen($word)) >= (double)Settings::get('min_proximity_ratio',$this->getWid())) {
            //return array();
        } else {
            foreach ($max->words as $w) {
                $word = new SearchWord($w->word);
                $word->removeEnds();
                if(!$this->isDontCare($word)) {
                    //$word->multiplyer = '0.5';
                
                    $out[] = 'SELECT \'' .$word.'\' as word, '.$w->rank.' as rank ';                  
                }
            } 
        }
        
        if(empty($out))
        {
            return 'SELECT word, rank FROM words where 0';
        }
        return implode(' UNION ',$out);
    }

   

}
