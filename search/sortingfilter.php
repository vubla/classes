<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}

class SortingFilter extends BaseFilter {
    
    private $sortDesc;
    private $sortBy;
    
    function __construct($wid,$fullSearch = null)
    {
        parent::__construct($wid,$fullSearch);
        $this->vdo = VPDO::getVDO(DB_PREFIX.$this->getWid());
        $this->sortDesc = true;
        $this->sortBy = null;
    }
    
    /**
     * In this class products should NOT keep the same order as before, for obvious reasons(look at the name of the class)
     */
    function getResults (array $product_ids) {
        return $this->filter($product_ids);
    }
    
    protected function filter(array $product_ids) {
        $additionalOrderBy = '';
        $joinSortTable = '';
        $sortBy = $this->sortBy;
        $sortDesc = $this->sortDesc;
        if(is_null($sortBy) || empty($product_ids)) {
            return $product_ids;
        }
        else 
        {
            if($sortBy == $this->getLowestPriceString()) {
                $sortType = 'number';
                $name = $this->getLowestPriceString();
            } else {
                $option = OptionHandler::getOptionSetting($this->getWid(),$sortBy);
                $sortType = $option->sortable;
                $name = $option->name;
            }
            $temp = new stdClass();
            $sortable = true;
            switch (strtolower($sortType)) {
                case 'number':
                    $temp->type = 'decimal(20,2)';
                    break;
                case 'string':
                    break;
                default: 
                    $sortable = false;
                    break;
            }
            if($sortable) {
                $temp->name = $name;
                $temp->aggregator = 'max';
                $joinSortTable = ' join ('.$this->getGenerateProductOptionsQuery(array($temp)).') sorter on sorter.product_id = p.id ';
            
                if($sortDesc) {
                    $additionalOrderBy = "max_$name DESC";
                } else {
                    $additionalOrderBy = "max_$name ASC";
                }
            }
        }
        
        $whereClause = '';
        if(!$this->getFullSearch())
        {
            $whereClause = 'WHERE '.$this->generateWhereClauseFromProductIds($product_ids);
        }
        $query = 
            'SELECT
                product_id './/$displayOptions.//', MAX(boosted) as boosted, SUM(inname) as inname, SUM(indesc) as indesc, SUM(incategory) as incategory 
             ' FROM (
                products p '.$joinSortTable.'
             ) 
              '.$whereClause.' 
            GROUP BY product_id ORDER BY ' . $additionalOrderBy;
        //var_dump($query); exit;
    
        ############# STOP BUILDING RANKING QUERY ###############
        ############# STOP BUILDING RANKING QUERY ###############
        ############# STOP BUILDING RANKING QUERY ###############
        ############# STOP BUILDING RANKING QUERY ###############
        ############# STOP BUILDING RANKING QUERY ###############
       
        
        ### Makes sure DB hasen't changed 
        $this->vdo->exec('USE '.DB_PREFIX.$this->getWid());
        ### The actual execution
        $result = $this->vdo->getTableList($query, '');
        if(!isset($result)) {
            $result = array();
        }
        $out = array();
        foreach ($result as $item) {
            $out[] = (int)$item->product_id;
        }
        
        return $out;
    }

    public function setSortOrder($string) {
        switch (strtolower($string)) {
            case 'asc':
            case 'ascending':
                $this->sortDesc = false;
                return true;
            
            case 'desc':
            case 'descending':
                $this->sortDesc = true;
                return true;
            
            default:
                
                return false;
        }
    }
    
    public function setSortBy($sortBy){
        if(is_string($sortBy) && $this->isRDisplayIdentifier($sortBy)){
            $this->sortBy = $sortBy;
            return true;
        }
        return false;
    }
}
?>