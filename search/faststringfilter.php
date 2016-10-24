<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}

class FastStringFilter extends  ConjunctiveStringFilter {    
    
    function __construct($wid,$search,$fullSearch = null){
        parent::__construct($wid,$search,$fullSearch);
    }
    
    protected function filter(array $product_ids){ 
        if(is_null($this->original)){
            return array();
        }
     
        /// If full search we search amoung everything, which here is equivalent to null.
        $searchAmong = $this->getFullSearch() ? null : $product_ids; 
       
       
        /// If empty query we just search right away.
        if($this->original == '')
        {
            return $this->result = $this->search(array(),$searchAmong);
        }
        
        /// Takes the words remove the ends and put them in the words to process array
        $this->original_search_array = $this->prepareWords($this->original);
        
   
        /// Initial search
        $this->result = $this->search($this->original_search_array,$searchAmong);

        return $this->result;

    }

  
    /**
     * @words Array of strings to search for
     *      if empty array is inserted, everything is found
     *      if non-array is inserted, nothing is found
     * @minOptions Array of strings which names the options to search for
     */
    protected function search($words,$product_ids = null,$fuzzyThres = 0) {
        ################## BUILDING RANKING QUERY ##################
        ################## BUILDING RANKING QUERY ##################
        ################## BUILDING RANKING QUERY ##################
        ################## BUILDING RANKING QUERY ################## 

        $qs = array();
        
        $productIdsWhere = '';
        if(is_array($product_ids))
        {
            if(empty($product_ids)) // No point in searching among nothing
            {
                return array();
            }
            $productIdsWhere = ' and ' . $this->generateWhereClauseFromProductIds($product_ids,'p','id');
        }
        if(!is_array($words)) // If some one puts in a string or object or something crazy, we just return empty array
        {
            return array();
        }
		$limit = Settings::get('suggestions',$this->getWid());
		$limitString = '';
		if($limit > 0) {
			$limitString = " LIMIT $limit";
		}

        if(empty($words)) // means that every product should be found
        {
            /**
             * Please notice that the order of arguments of this select actually matters!
             * p.pid have been placed later, which resulted in no image link when grouping later on
             */
            $query = 
            '(SELECT 
                p.id as product_id, 0 as boosted, 0 as inname , 0 as indesc, 0 as incategory 
            FROM 
                products p
            WHERE
                1 = 1
                '.
                $productIdsWhere. ')';           
            $qs[] = $query;  
            
        }
        else 
        {
            foreach($words as $word) {
                $this->words_i_search_for[] = $word;
                /**
                 * Please notice that the order of arguments of this select actually matters!
                 * p.pid have been placed later, which resulted in no image link when grouping later on
                 */
                $query = 
                '(SELECT 
                    p.id as product_id, MAX(p.boosted) as boosted, SUM(inname) * ' .$word->getMultiplyer() .' as inname , SUM(indesc) * ' .$word->getMultiplyer() .' as indesc, SUM(incategory) * ' . $word->getMultiplyer() . ' as incategory 
                FROM 
                    products p
                    inner join word_relation wr 
                        on wr.product_id = p.id 
                    inner join words w 
                        on w.id = wr.word_id
                WHERE 
                	wr.inname > 0 and
                    w.word LIKE ' .$this->vdo->quote($word->short.'%').
                    $productIdsWhere. ' 
                GROUP BY p.id)';           
                $qs[] = $query;  
            }   
        }

        # Le grande Finale
        
        $this->query = 
            'SELECT product_id FROM
             (SELECT
                product_id as product_id, MAX(boosted) as boosted, SUM(inname) as inname, SUM(indesc) as indesc, SUM(incategory) as incategory '. 
             ' FROM (' . 
            implode( "\n UNION ALL \n", $qs) . ')  AS innermost 
            GROUP BY product_id HAVING count(*) >= '.sizeof($words).' ORDER BY boosted desc, inname desc, incategory desc, indesc desc ) AS middle '.
            $limitString;
    
        ############# STOP BUILDING RANKING QUERY ###############
        ############# STOP BUILDING RANKING QUERY ###############
        ############# STOP BUILDING RANKING QUERY ###############
        ############# STOP BUILDING RANKING QUERY ###############
        ############# STOP BUILDING RANKING QUERY ###############
       
        
        ### Makes sure DB hasen't changed 
        $this->vdo->exec('USE '.DB_PREFIX.(int)$this->getWid());

        ### The actual execution
        $result = $this->vdo->getTableList($this->query, '');
        if(is_null($result)) {
            return array();
        }
		$out = array();
		foreach ($result as $item) {
			$out[] = (int)$item->product_id;
		}

        return $out;
    }

	public function query() {
		return $this->query;
	} 
}