<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}

abstract class AnySearchObject {
    
    
    private $wid; 
    protected $vdo;
    
    function __construct($wid){
        if($wid < 1){
            throw new VublaException("Missing wid ". print_r($wid, true));
        }
        $this->wid = $wid;
        $this->vdo = VPDO::getVDO(DB_PREFIX.$this->getWid()); 
    }
    
    final function getWid(){
        return $this->wid;
    }

    function expandSearch(){
        $stopAt = sizeof($this->original_search_array) - 1; // Is calculated prior to the for loop. Else it will run forever. 
        // Concat the words by the preceding and postceding
        for($i = 0; $i <  $stopAt; $i++){
            $word = $this->original_search_array[$i] . $this->original_search_array[$i + 1];
            //$this->words_i_search_for[] = new SearchWord($word);
            
        }   
    }
        
    function prepareWords($string) {
        $wordlist = explode(" ", trim($string));
        $result = array();
        //var_dump($wordlist);
                
        /// Chop up words and put in array;
        foreach($wordlist as $w){
            $word = SearchWord::loadFromDb($w,$this->wid);
            $word->removeEnds();
            if($this->isDontCare($word)) {
                $word->multiplyer = '0.5';
            }
            $result[] = $word;
        }
        
        $result = array_unique($result);
        return $result;
    }
    
    protected function isDontCare($word) {
        $short = $word->short;
        $long = $word->word;
        
        $where = '';
        $vdo = VPDO::getVDO(DB_METADATA);
        
        if(strlen($short) > 0) {
            $where .= 'WHERE word = ' . $vdo->quote($short);
            if(strlen($long) > 0) {
                $where .= ' OR word = ' . $vdo->quote($long);
            }
        } elseif(strlen($long) > 0) {
            $where .= 'WHERE word = ' . $vdo->quote($long);
        }
        
        $sql =  "SELECT id ".
                "FROM dontcarewords ".
                $where;
                
        $result = $vdo->fetchOne($sql);
        
        return isset($result);
    }
    
    function resolveWid($host)
    {
        return HttpHandler::resolveWid($host);
    }
    
    public function __get($name){
        if(isset($this->$name)){
            return $this->$name;
        } elseif(isset($_GET[$name])) {
            $ss = $_GET[$name];
            return $ss;
        }  elseif(isset($_GET['getvar']->$name)){
            $ss =  $_GET['getvar']->$name;
            if(is_object($ss)){
                return (array)$ss;
            }
            return $ss;
        } elseif(isset($_GET['postvar']->$name)){
           $ss =  $_GET['postvar']->$name;
           if(is_object($ss)){
                return (array)$ss;
           }
           return $ss;
        }
        return null;
    }

    public function getSearchVar($name)
    {
        $ss = null;
        if(isset($_GET[$name])) {
            $ss = $_GET[$name];
           
        }  elseif(isset($_GET['getvar']->$name)){
           $ss =  $_GET['getvar']->$name;
        
        } elseif(isset($_GET['postvar']->$name)){
           $ss =  $_GET['postvar']->$name;
         
        }
        if(is_object($ss)){
            return (array)$ss;    
        }
        return $ss;
        
    }

    /**
     * Figures out if wat should be applied using, settings and vubla_enable_vat
     * Using haskell type inference notation: int -> int -> int
     * @param $wid 
     * @return a function identifier which takes the params: (int price, int vat multiplyer)
     */ 
    public final function GetVatfunc($wid,$invert = false)

    {
        
        $vat_multiply_func = function ($p,$v)
        {
            return $p * $v;
        };
        
        $vat_divide_func = function ($p,$v)
        {
            return $p / $v;
        };
        
        $vat_identity_func = function ($p,$v)
        {
            return $p;
        };
        
        $prices_stored_with_vat = settings::get('prices_stored_with_vat', $wid);
        if($this->vubla_enable_vat)
        {
            
            if($prices_stored_with_vat)
            {
                // Prices are stored with vat and should be showed with vat
                $vat_func = &$vat_identity_func;
            } 
            else 
            {
                // Prices are stored without vat and should be showed with vat
                if($invert) {
                    $vat_func = &$vat_divide_func;
                } else {
                    $vat_func = &$vat_multiply_func;
                }
            }
        } 
        else
        {
            if($prices_stored_with_vat)
            {
                // Prices are stored with vat and should be showed without vat
                if($invert) {
                    $vat_func = &$vat_multiply_func;
                } else {
                    $vat_func = &$vat_divide_func;
                }
            } 
            else 
            {
                // Prices are stored without vat and should be showed without vat
                $vat_func = &$vat_identity_func;
            }
        } 
        return $vat_func;
    }
}


