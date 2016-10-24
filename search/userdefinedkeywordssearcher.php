<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}

class UserDefinedKeywordsSearcher extends AnySearchObject {
    
    // The original search Query
    public $original = null;
        
    /**
     * An array of errors.
     */
    public $errors = array();
    
    
    /**
     * The results in an array
     */
    public $result;
	
	function __construct($wid,&$search){
        parent::__construct($wid);
        $this->original = $search;
    }

	
	
	function getResults() {
		if(is_null($this->original)){
			return array();
		}

		// Takes the words remove the ends and put them in the words to process array
		$searchWords = $this->prepareWords($this->original);
        //var_dump($searchWords);

		foreach($searchWords as $word){
            //Please notice that the order of arguments of this select actually matters!
			$query = '(SELECT text, url FROM user_defined_keywords keywords inner join word_keywords wk on wk.keyword_id = keywords.id WHERE wk.word = ' .$this->vdo->quote($word->short). ' ) ';			
			$qs[] = $query;
			
		}
		
		$qs[] = '(SELECT text, url FROM user_defined_keywords keywords inner join word_keywords wk on wk.keyword_id = keywords.id WHERE wk.word like ' .$this->vdo->quote("%".$this->original."%"). ' ) ';			


		### end price sort query	
		
		$this->query = 'SELECT DISTINCT text, url FROM (' . implode( "\n union \n", $qs) .') as dasdasdasdasda limit 50';
        //var_dump($this->query);
		############# STOP BUILDING RANKING QUERY ###############
				
		
		/// Makes sure DB hasen't changed 
		$this->vdo->exec('USE '.DB_PREFIX.(int)$this->getWid());
	
		/// Get the result
		$this->result = $this->vdo->getTableList($this->query, '');
		
		return $this->result;
	}

	
	function getSuggestions($word) {
		return null;
	}
	
    
    public  function generateHtml($real_host, $file = '', $param = 'keywords', $layout){

        $result = &$this->result;
        $out ='';
    	$content = $layout;
        	
		$parts = explode("[@nøgleord_start]", $content);
		$pre_list = $parts[0];
		if(!isset($parts[1])){
		   $layout = str_replace("[@nøgleord_slut]", '', $layout);  
          return  $layout = str_replace("[@nøgleord_text]", '', $layout);
		} 
		$parts = explode("[@nøgleord_slut]", $parts[1]);
		$list = $parts[0];
		$post_list = $parts[1];
		$out .= $pre_list;
		$length = (int)Settings::get('keyword_text_lenght', $this->getWid());

        if(isset($result) && sizeof($result) > 0){
			foreach($result as $row){
    			$list_temp = $list;
				
		        $text_short = substr(strip_tags($row->text),0,$length);
		        
    			$list_temp = str_replace("[@nøgleord_text]", '<a href="'.$row->url.'">'.$text_short.'</a>', $list_temp);
				
    			$out .= $list_temp;
    		}
        }
			
			
		$out .= $post_list;

    	return $out;
    
    }
}

