<?php

abstract class SpellingCorrector extends WordCorrector
{
    public function __construct($wid,$asSql = null)
    {
        parent::__construct($wid,$asSql);
    }
    
    static function create($wid)
    {
        $spelling_algorithm = settings::get('search_spelling_algorithm', $wid);
        switch ($spelling_algorithm)
        {
            default:
                return new LevenshteinSpellingCorrector($wid);
        }
        
    }

}
