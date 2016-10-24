<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}
/**
 *
 * Enter description here ...
 * @author rasmus
 *
 */
class SearchWord extends Word {


    public $rank = 0;
   
	public $multiplyer = null;
    static function loadFromDb($string, $wid)
    {
        $stm = vdo::webshop($wid)->prepare("SELECT id,word,rank FROM words WHERE  word = ?");
        $stm->execute(array($string));
        $word = $stm->fetchObject('SearchWord');
        $stm->closeCursor();
        if(!is_object($word))
        {
            $word = new SearchWord($string);
        }
        else 
        {
            $word->word = $string;
            $word->rank = (int)$word->rank;
        }
       
        return $word;
    }
    

	function __construct($word = null, $multi = '1'){
	    if(is_object($word)) 
	    {
	        parent::__construct($word->word);
            $this->rank = $word->rank;
	    }
        else
        {
		  parent::__construct($word);
        }
		$this->multiplyer = $multi;
		
	}
	
	function getMultiplyer(){
	   return $this->multiplyer;
	}


    public function getWithEnding($org = null){
        if(!is_null($this->word)){
            return $this->word;
        } else if(!is_null($org)){
            return $this->short.'-';
            $lenght = strlen($org->short);
            $ending = $org->ending;
            $w = $this->short . $ending;
            $pspell_link = pspell_new("da");
            $new = null;
            
            if (!pspell_check($pspell_link, $w)) {
               
                $suggestions = pspell_suggest($pspell_link, $w);
                foreach($suggestions as $s){
                    if(strpos($s,' ') !== false){
                        continue;
                    }
                    //echo substr($w, 0, $lenght) .'=='. substr($s,0,$lenght);
                  
                    if(substr($w, 0, $lenght) == substr($s,0,$lenght)){
                        $new = $s;
                    }

                }

            }
            if(is_null($new)){
                $w = $this->short.'-';
            }
            return $w;

           
        } else {
            return $this->short.'-';
        }
        
    }
    
    
    function __toString(){
	 $returnable = "";
	 if(!is_null($this->word)){
            $returnable = $this->word;
        } else {
            $returnable = $this->short;
        }
	if(!is_string($returnable))
	{
		return "";
	}
	return $returnable;
	}
	
	
}

?>
