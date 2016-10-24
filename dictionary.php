<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}
class Dictionary {
	
	
		
	static function getThesaurus(Word $word,$max = 50){
	   
	    $len = strlen($word->short);
	    $q = 'SELECT t2.word 
            FROM  `thesaurus` t1
            JOIN thesaurus_links l ON l.entrance = t1.id
            JOIN thesaurus t2 ON t2.id = l.out
            WHERE  t1.short like ?  
            LIMIT '. (int)$max;
	    $stm = Vpdo::getVdo(DB_METADATA)->prepare($q);
	    $stm->execute(array($word->short));
	    $list = $stm->fetchAll();
        $stm->closeCursor();
	    $wordlist = array();
	    foreach($list as $word){
	        $wordlist[] = new SearchWord($word["word"]);
	    }
	    //var_dump($wordlist); exit;
	    return $wordlist;
	}	
	
	static function refreshDontCareWords($path){
	    $db = Vpdo::getVdo(DB_METADATA);
        $db->exec('truncate table dontcarewords');
   
        $from_encoding = "ISO-8859-1";
        $to_encoding = "UTF-8";
           
        $words =  file_get_contents($path);
       // $words = iconv($from_encoding, $to_encoding, $words);
        
       // $words = utf8_decode($words);
        $words_array = explode("\n", $words);
        foreach($words_array as $word){
            $word = new Word($word);
            $sql = 'INSERT INTO dontcarewords (word) values ('.$db->quote(strtolower($word->short)).')';
            $db->exec($sql);
        
        }
        
        
	
	
	}
	
    static function refreshThesaurus($path){
        new Word();
        
        $db = Vpdo::getVdo(DB_METADATA);
        $db->exec('truncate table thesaurus');
        $db->exec('truncate table thesaurus_links');
        $from_encoding = "ISO-8859-1";
        $to_encoding = "UTF-8";
           
        $thesaurus =  file_get_contents($path);
        $thesaurus = iconv($from_encoding, $to_encoding, $thesaurus);
        $ignored = array();
        $lines = explode("\n", $thesaurus);
        foreach($lines as $line){
            if(substr($line, 0,1) == "#"){
                $ignored[] = $line;
                continue;
            }
            $words = explode(";", $line);
            $ids = array();
            $words_classes = array();
            foreach($words as $word_string){
                if(is_null($word_string)){
                    continue;
                }
                $word = preg_replace("/\([^\)]+\)/","",$word_string);
                $word = new ThesaurusWord($word);
                $word->removeEnds(); //Maybe not?
                
                if(strlen($word->short) < 1){
                    $ignored[] = $word;
                    continue;
                }
               
                $isDontCare = $db->fetchOne('select count(*) from dontcarewords where word = ?', array($word->short));
           
				if($isDontCare > 0){
					$ignored[] = $word;
					continue;
				}
                $id = $db->fetchOne('select id from thesaurus where short = ?', array($word->short));
				
                if(!isset($id)){
                    $stm = $db->prepare('insert into thesaurus (short, word) values (?,?) ');
                    $stm->execute(array($word->short, $word->word));
                    $id = $db->fetchOne('select id from thesaurus where short = ?', array($word->short));
                    
                    $word->tid = $id;
                
                    $words_classes[] = clone $word;

                } else {
                    $word->tid = $id;
                }
                $ids[] = $id;
				
				
				$word = null;
                
            }
            $ids = array_unique($ids);
            foreach($words_classes as $word){
                foreach($ids as $id){
                        
                       // echo 'select count(*) from thesaurus_links where `entrance` = '.(int)$word->tid.' and `out` = '.(int)$id;
                    // if($count = $db->fetchOne('select count(*) from thesaurus_links where `entrance` = '.(int)$word->tid.' and `out` = '.(int)$id)){
                    //    $dubs[] = "$word->short\n";
                    // } else {
               
                        if($id != $word->tid){
                            $stm = $db->prepare('insert into thesaurus_links (`entrance`, `out`) values ('.$word->tid.",".$id.")");
                            if(!$stm->execute()) {
                                var_dump($id);
                                var_dump($word->tid);
                                var_dump($word->short);
                            }
                            $stm = $db->prepare('insert into thesaurus_links (`entrance`, `out`) values ('.$id.','.$word->tid.')');
                            if(!$stm->execute()) {
                                var_dump($id);
                                var_dump($word->tid);
                                var_dump($word->short);
                            }
                            $stm->closeCursor();
                        }
                    //}
                
                }
            }
            
       
        }
        //var_dump($dubs);
       //  var_dump($ignored);
        
    
    }


	
	
}