<?php

class SynonymCorrector extends WordCorrector
{
    public function __construct($wid,$asSql = null)
    {
        parent::__construct($wid,$asSql);
    }
    
    /**
     * @param $word Word
     * @return sql that gives coorrection of the word
     */
    protected function _correctWord($word)
    {
        $out = array();
        $syns = Dictionary::getThesaurus($word);
        if(empty($syns))
        {
            return array();
        }
        $vdo = &$this->vdo;
        $syns = array_map(function ($a) use($vdo) { return $vdo->quote($a); }, $syns);
       
       
        $sql = 'SELECT id, word, rank FROM `words` WHERE word in ('.implode(',', $syns).') and word not in (select word from '. DB_METADATA .'.dontcarewords)';
        
        $result = $this->vdo->getTableList($sql,'SearchWord' );
       // var_dump($result);
        if(is_null($result))
        {
            return array();
        }
        return $result;
         
    }
}
