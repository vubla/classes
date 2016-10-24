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



class Word {

    static $nonallowableChars = array(';','&',"’","'",'"',
    " "," ",
    ":",
    "“",
    "®",
    "%",
    "•",
    "·",
    " ", "º", "<", ">", " ", "”","*", "|", "½", "°", "_");
    
    static $addedAll = false;
    
    static function getNonAllowableChars()
    {
           if(!self::$addedAll)
            {
                mb_internal_encoding('UTF-8');
                mb_regex_encoding('UTF-8');
               ///*
               
                $sec = array("ad","9c","a2","a9","a8","9d", "99");
                foreach($sec as $k=>$w)
                {
                    Word::$nonallowableChars[] = chr(hexdec($w));
                }
                // */
                self::$addedAll = true;
                Word::$nonallowableChars = array_unique(Word::$nonallowableChars);
            }
            return Word::$nonallowableChars;
    }
    
	public $word;
    private $id;
	 
	/**
	 * Private, but can be accessed through __get and __set
	 * 
	 */
	private $short;
	
    public $ending;
	
	function __construct($word = '',$id =null){
        $this->word = trim($word);
        $this->id = $id;
		
	}

	function __toString(){
		return $this->word;

	}
	
	function __get($name){
	   if($name == 'short'){
	       if(is_null($this->short)){
	           $this->removeEnds();
	       }
	       return $this->short;
	   }
	}
    function __set($name, $value){
	   //if($name == 'short'){
	       $this->$name = $value;
	       
	   //}
	}

	/**
	 *
	 * Removes ends and illigal charaters
	 * @param unknown_type $this->word
	 */
	function removeEnds(){
		if(empty($this->short)){
			
    		$this->short = $this->word;
     
    		
    		$min_size = 3; // with this, searching for Bo might be troublesome :P
    		
        		
    		foreach(array("–","­", "-") as $hyphen){
                 $this->short = self::trimHyphen($this->short, $hyphen);
                 $this->short = mb_replace($hyphen, '-', $this->short) ;        
               
            }
         
       
            $this->short = mb_replace('é','e', $this->short ) ;
            $this->short = mb_replace(Word::getNonAllowableChars(), '', $this->short) ;
    		$this->short = trim(mb_strtolower($this->short));
       
            if (mb_detect_encoding( $this->short, 'UTF-8', true) === FALSE) { 
                 $this->short = utf8_encode( $this->short); 
            }
    
		}

		
		
		return $this->short;
	}
    
    static function trimHyphen($w, $hyphen = '-')
    {
        
        if(mb_substr( $w, -1,1) == $hyphen){
            $w =  mb_substr( $w,0, -1) ;
        }
        if(mb_substr( $w, 0,1) == $hyphen){
            $w = mb_substr($w, 1) ;       
        } 
        return $w;
        
    }

	 function save($product_id, $field){
	 	
 		$word_table = "words".ScrapeMode::getPF();
 		$word_rel_table = "word_relation".ScrapeMode::getPF();
        if(class_exists('Scraper'))
        {
            $wid = Scraper::$static_wid;
        } 
        else 
        {
            $wid = ProductFinder::$WID;
        }
	 	$vdo = VPDO::getVdo(DB_PREFIX . $wid);

		$this->removeEnds();
        if(empty($this->short))
        {
            return;
        }
		if($this->short == ''){
			return ;
		}
		$comp_op = "like";
		$sql = "SELECT EXISTS(SELECT 1 FROM $word_table WHERE word $comp_op  ".$vdo->quote($this->short).")";
		if($vdo->fetchOne($sql) == 0)
		{
			
			$sql = "INSERT INTO $word_table (word, word_temp) VALUES (".$vdo->quote($this->short).",". $vdo->quote($this->word).")";
			$rows_affected = $vdo->exec($sql);
            if($rows_affected != 1)
            {
                VOB::_n("With the word: " . $this->word . " we did not get it inserted");
            }
		}
		$sql = 'SELECT ' .
				'EXISTS( ' .
					' SELECT 1 ' .
					'FROM ' . $word_rel_table . ' ' .
					"WHERE word_id = ( " .
						"SELECT id " .
						"FROM $word_table " .
						"WHERE word $comp_op ".$vdo->quote($this->short).
					') '. 
					'AND product_id = ' . (int)$product_id . 
				')';
	
		if($vdo->fetchOne($sql) == 0){
			$sql = "INSERT ".
						"INTO $word_rel_table (product_id, word_id, $field) 
						VALUES (
							".$vdo->quote($product_id).",
							(
								SELECT id " .
								"FROM $word_table " .
								"WHERE word $comp_op  ".$vdo->quote($this->short).
							"), 
							'1')";
			$rows_affrected = $vdo->exec($sql);
            
            ###### TEMP DEBUG #####
            if($rows_affrected != 1)
            {
                VOB::_n("With the word: " . $this->word . " we did not get it inserted");
            }
            $word = $vdo->fetchOne("select word from $word_table where word $comp_op  ".$vdo->quote( $this->short));
            if($word != $this->short)
            {
                 VOB::_n("Unable to find this word: '" . $this->word . "' ".bin2hex($this->word)." with this short: '" . $this->short . "'.".bin2hex($this->short)." We found this instead: '" . $word ."'" .  bin2hex($word));
                   VOB::_n("select word from $word_table where word $comp_op  ".$vdo->quote( $this->short));
            ##### TEMP DEBUG END #####
            }
		} else {
			$sql = "UPDATE $word_rel_table
						SET $field = $field + 1 
						WHERE product_id = $product_id 
						AND word_id = (
							SELECT id 
							FROM $word_table 
							WHERE word $comp_op ".$vdo->quote($this->short) . ")";
			$vdo->exec($sql);

		}

	}
	

}

class ThesaurusWord extends Word{
    public  $tid;

}

