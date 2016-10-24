<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}

abstract class WordSelector extends  AnySearchObject  
{
    protected $stdNumber = 3;
    abstract function selectWords(array $words,$number = 0);
    
    protected function sort(array $wordsSets)
    {
        return $wordsSets;
    } 
    
    public function selectWordArrays(array $wordsSet, $number = 0)
    {
        if($number <= 0)
        {
            $number = $stdNumber;
        }
        $innerNumber = ($number);///(sizeof($wordsSet));
        
        $correctedWords = array();
        $total = 1;
        $counters = array();
        foreach ($wordsSet as $key => $words) 
        {
            $temp = array();
            $word = SearchWord::loadFromDb($key,$this->wid);
            $temp[] = $word;
            /*if($word->rank > 0)
            {
                $temp[] = $word;
            }*/
            foreach ($this->selectWords($words,$innerNumber) as $correctedword) 
            {
                $temp[] = $correctedword;
            }
            /*if(empty($temp))
            {
                $temp[] = $word;
            }
            */
            $total *= sizeof($temp);
            //Not sure if the following line should actually be here or outside the if(!empty)
            $counters[] = 0;
            $correctedWords[] = $temp;
        }
        $result = array();
        $arrayNum = 0;
        $totalArrays = sizeof($correctedWords);
        if($totalArrays <= 0) return array();
        $max = min(array($total));
        for($i = 0 ; $i < $max ; $i++)
        {
            $temp = array();
            $j = 0;
            foreach($correctedWords as $words)
            {
                $temp[] = $words[$counters[$j]];
                $j++;
            }
            $result[] = array_unique($temp);
            $cur = $totalArrays-1;
            $counters[$cur]++;
            while($counters[$cur] >= sizeof($correctedWords[$cur]))
            {
                $counters[$cur] = 0;
                $cur--;
                if($cur < 0) break 2;
                $counters[$cur]++;                
            }
        }
        
        return array_slice($this->sort($result),0,$number);
    }
}