<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}

class RankingWordSelector extends WordSelector  
{
    function selectWords(array $words, $number = 0)
    {
        if($number <= 0)
        {
            $number = $stdNumber;
        }
        if(!usort($words,'RankingWordSelector::compareSingle'))
        {
            throw new VublaException('Failed to sort words');
        }
        return array_slice(array_filter($words, function ($element) { return ($element->rank > 0); } ), 0,$number);
    }
    
    private static  function compareSingle($a,$b)
    {
        return $b->rank - $a->rank; //Usually the other way around, but wee want descending
    }
    
    private static  function compareMultiple($as,$bs)
    {
        $result = 0;
        foreach ($as as $a) {
            $result -= $a->rank;
        }
        
        foreach ($bs as $b) {
            $result += $b->rank;
        }
        
        return $result;
    }
    
    protected function sort(array $wordsSets)
    {
        if(!usort($wordsSets,'RankingWordSelector::compareMultiple'))
        {
            throw new VublaException('Failed to sort words');
        }
        return $wordsSets;
    } 
}