<?php

class CharacterSwapperCorrector extends WordCorrector
{
    public function __construct($wid)
    {
        parent::__construct($wid);
    }
    
    /**
     * @param $word Word
     * @return sql that gives coorrection of the word
     */
    protected function _correctWord($word)
    {
        $startString = $word->word;
        $swappedStrings = array();
        $size = strlen($startString);
        for($i = 0 ; $i < $size-1 ; $i++)
        {
            $newString = $startString;
            $tempChar = $newString[$i];
            $newString[$i] = $newString[$i+1];
            $newString[$i+1] = $tempChar;
            $swappedStrings[] = $newString;
        }
        $vdo = $this->vdo;
        $swappedStringsQuoted = array_map(function ($elem) use($vdo) {return $vdo->quote($elem);}, $swappedStrings);
        
        
        if(empty($swappedStringsQuoted)) return array(); // Hotfix by Rasmus 20130311
        
        $sql = 'SELECT word,rank FROM words where word in ('.implode(', ', $swappedStringsQuoted).') and word not in (select word from '. DB_METADATA .'.dontcarewords)'; 
        
        $result = $this->vdo->getTableList($sql,'SearchWord' );

        if(is_null($result))
        {
            return array();
        }
        return $result;
    }
}
