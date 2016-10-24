<?php
class UserDefinedKeyword {
    
    public static function insertKeywordsAndText($keywords, $text, $link, $wid) {
        $vpdo = VPDO::getVDO(DB_PREFIX.$wid);        
        ############ INSERT KEYWORD IN USER_DEFINED_KEYWORDS
        $vpdo->exec("INSERT INTO user_defined_keywords(text,url) VALUES (".$vpdo->quote($text).",".$vpdo->quote($link).')');
        $result = $vpdo->fetchOne("SELECT id FROM user_defined_keywords WHERE text = ?",array($text));
        if(!$result) {
            throw VublaException('Unable to find newly inserted user defined keyword');
        }
        $keywordId = $result;
        
		foreach ($keywords as $keyword) {
        	self::addKeywordToText($keyword,$keywordId,$wid);
		}
    }
    
    public static function removeKeywordFromText($keyword,$textId,$wid) {
        $vpdo = VPDO::getVDO(DB_PREFIX.$wid);
        
        /*
        $result = $vpdo->getTableList("SELECT keyword_id FROM word_keywords where word_id = ?",array($wordId));
        foreach ($result as $row) {
            
        }*/
        //The following will leave some text behind when the last keyword for the text is deleted
        $vpdo->beginTransaction();
        $result = $vpdo->exec("DELETE FROM word_keywords where word = ".$vpdo->quote($keyword)." and keyword_id = ".$vpdo->quote($textId));
        if($result > 1 ) {
            $vpdo->rollback();
            throw new VublaException("More than one entry deleted from word_keywords");
        }
        $vpdo->commit();
    }

    public static function addKeywordToText($keyword,$textId,$wid) {
        if(!$keyword || !$textId || !$wid) {
            throw new VublaException("Invalid input");
        }
        
        $vpdo = VPDO::getVDO(DB_PREFIX.$wid);
        
        
        $vpdo->beginTransaction();
        $result = $vpdo->exec("INSERT INTO word_keywords (word,keyword_id) VALUES (".$vpdo->quote($keyword).",".$vpdo->quote($textId).')');
        if($result != 1 ) {
            $vpdo->rollback();
            throw new VublaException((int)$result." entries where inserted into word_keywords, when 1 was expected.");
        }
        $vpdo->commit();
    }
    
    public static function getUserDefinedKeyword($keywordId,$wid) {
        $vpdo = VPDO::getVDO(DB_PREFIX.$wid);
        
        $result = $vpdo->getRow("SELECT id,text,url FROM user_defined_keywords where id = ?",array($keywordId));
        
        $result->words = array();
        $words = $vpdo->getTableList("SELECT word FROM user_defined_keywords ud inner join word_keywords wk on ud.id = wk.keyword_id where ud.id = ?",null,array($result->id));
        
        foreach ($words as $word) {
            $result->words[] = $word->word;
        }
        
        return $result;
    }
    
    public static function getAllUserDefinedKeywords($wid) {
        $vpdo = VPDO::getVDO(DB_PREFIX.$wid);
        
        $result = $vpdo->getTableList("SELECT id,text,url FROM user_defined_keywords");
        //var_dump($result);
        foreach ($result as $row) {
            $row->words = array();
            $words = $vpdo->getTableList("SELECT word FROM user_defined_keywords ud inner join word_keywords wk on ud.id = wk.keyword_id where ud.id = ?",null,array($row->id));
            //var_dump($words);
            foreach ($words as $word) {
                $row->words[] = $word->word;
            }
        }
        return $result;
    }
    
    public static function setUserDefinedKeyword($data,$wid) {
        $vpdo = VPDO::getVDO(DB_PREFIX.$wid);
        
        
        if($data->id) {
            $result = $vpdo->exec("DELETE FROM word_keywords WHERE keyword_id = ".$vpdo->quote($data->id));
            $updateCol = '';
            foreach ($data as $name => $value) {
                if($name == 'words') {
                    foreach ($value as $word) {
                        self::addKeywordToText($word, $data->id, $wid);
                    }
                } else {
                    $updateCol .= $name . "=" . $vpdo->quote($value ). ',';
                }
            }
            $updateCol = substr($updateCol,0,strlen($updateCol)-1);
            $result = $vpdo->exec("UPDATE user_defined_keywords SET ".$updateCol." WHERE id = ".$vpdo->quote($data->id));
        } else {
            self::insertKeywordsAndText($data->words, $data->text, $data->url, $wid);
        }
    }
	
	public static function removeUserDefinedKeyword($id,$wid) {
		if(!$id || !$wid) {
            throw new VublaException("Invalid input");
		}
        $vpdo = VPDO::getVDO(DB_PREFIX.$wid);
        var_dump($id);
        $result = $vpdo->exec("DELETE FROM word_keywords WHERE keyword_id = ".$vpdo->quote($id));
		$vpdo->beginTransaction();
        $result = $vpdo->exec("DELETE FROM user_defined_keywords WHERE id = ".$vpdo->quote($id));
		
        if($result != 1 ) {
            $vpdo->rollback();
            throw new VublaException((int)$result." entries where deleted from word_keywords, when 1 was expected.");
        }
        $vpdo->commit();

    }
}
?>