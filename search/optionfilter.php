<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}

class OptionFilter extends BaseFilter 
{
	private $options;
	public $LOWEST_PRICE_STRING = 'lowest_price';
    
    function __construct($wid, $fullSearch = null)
    {
        parent::__construct($wid,$fullSearch);
        $this->vdo = VPDO::getVDO(DB_PREFIX.$this->getWid());
        $this->options = new stdClass();
		$this->options->min = array();
		$this->options->max = array();
		$this->options->eq = array();
    }

    protected function filter(array $product_ids)
	{
	    if(is_null($product_ids) || empty($product_ids))
	    {
	        return array();
	    }
      
        $this->handleVat();
        /*
        $price_filters = array();
        $filters = array();
        $eq_options = array();
        $where = "";
        if(isset($this->options->min['lowest_price']))
        {
            $price_filters[] = "lowest_price >= ". $this->options->min['lowest_price'];
            ;
        }
        if(isset($this->options->max['lowest_price']))
        {
            $price_filters[] = "lowest_price <= ". $this->options->max['lowest_price'];
          
        }
        
        if(!empty($price_filters)){
            $price_query = "select product_id from product_options where  " . implode($price_filters, ' and ');
        }
        
        foreach ($this->options->eq as $key=>$array_of_values)
        {
            if($this->isOption($key)) {
                $filters_or = array();
                foreach($array_of_values as $value) {
                    $filters_or[] = "`max_$key` LIKE " . $this->vdo->quote($value);
                }

                $temp = new stdClass();
                $temp->name = $key;
                $temp->aggregator = 'max';
                $eq_options[] = $temp;
                $filters[] = ' ( '. implode(' or ', $filters_or) . ' ) ';
                $option_not_null_checks[] = 'max_'. $key.' is not null';
            }
            
        }
       
        if(empty($price_filters)  && empty($eq_options))
        {
           return $product_ids;
        }
        if(empty($eq_options))
        {
           return $this->vdo->fetchSingleArray($price_query .' and product_id in (' . implode(',', $product_ids) . ')');
        }
        
        if(isset($price_query)){
            $query = 'select * FROM ';
            $query .= '(' . 
                    $price_query;
            $query .= ' )  priceq join ';
           
            $query .= '(' . 
                    $this->getGenerateProductOptionsQuery($eq_options, 'id');
            $query .= ' ) eqq on priceq.product_id = eqq.product_id ';
        } else {
             $query = 'select * FROM (';
             $query .= $this->getGenerateProductOptionsQuery($eq_options, 'id') . ') eqq ';
        }
        
        
        if(!empty($filters))
        {
             $query .= ' where '.implode(' and ', $option_not_null_checks).
             ' and '. implode(' and ', $filters). ' and ';
        } else {
                $query .= ' where ';
        }
        $query .= '(eqq.product_id in (' . implode(',', $product_ids) . '))';
       
        
        $result = $this->vdo->fetchSingleArray($query);
        
        if(is_null($result)){
            return array();
        }
       // Applies the filter
	 //  $filtered_ids = array_filter($product_ids, $this->getFilter());	
    /*/
       $filters = array();
       $max_options = array();
       $min_options = array();
       $eq_options = array();
       $option_not_null_checks = array();
       foreach ($this->options->min as $key => $value) {
          if($this->isOption($key)) {
              if($this->getSearchVar($key.'_slider_active') == 'no') continue;
              $min_key_bottom_name = 'min_'.$key .'_bottom';
              if(!is_null($this->$min_key_bottom_name) && $this->$min_key_bottom_name == $value) continue; /// This check if its already at buttom
              $filters[] = "CAST(`max_$key` AS decimal(20,2)) >= " . $this->vdo->quote($value);
              $temp = new stdClass();
              $temp->name = $key;
              $temp->type = 'decimal(20,2)';
              $temp->aggregator = 'max';      // <-- This may seem weird, but intuitive I would say that we always want the MAX of some option to above the minimum limit
              $min_options[] = $temp;
              $option_not_null_checks[] = 'max_'.$key.' is not null';
          } else {
              //error
          }
      }
      
      foreach ($this->options->max as $key => $value) {
            if($this->isOption($key)) {
                if($this->getSearchVar($key.'_slider_active') == 'no') continue;
                $max_key_top_name = 'max_'.$key .'_top';
                if(!is_null($this->$max_key_top_name) && $this->$max_key_top_name == $value) continue; /// This check if its already at buttom
                $filters[] = "CAST(`min_$key` AS decimal(20,2)) <= " . $this->vdo->quote($value);
                $temp = new stdClass();
                $temp->name = $key;
                $temp->type = 'decimal(20,2)';
                $temp->aggregator = 'min';
                $max_options[] = $temp;
                $option_not_null_checks[] = 'min_'.$key.' is not null';
            } else {
                //error
            }
        }
        
        foreach ($this->options->eq as $key=>$array_of_values)
        {
            if($this->isOption($key)) {
                $filters_or = array();
                foreach($array_of_values as $value) {
                    $filters_or[] = "`max_$key` LIKE " . $this->vdo->quote($value);
                }

                $temp = new stdClass();
                $temp->name = $key;
                $temp->aggregator = 'max';
                $eq_options[] = $temp;
                $filters[] = ' ( '. implode(' or ', $filters_or) . ' ) ';
                $option_not_null_checks[] = 'max_'.$key.' is not null';
            }
            
        }
        $query = 'select * FROM ';
        if(empty($min_options) && empty($max_options) && empty($eq_options))
        {
           return $product_ids;
        }
        $query .= '(' . 
                $this->getGenerateProductOptionsQuery($min_options);
        $query .= ' )  minq join ';
        $query .= '(' . 
                $this->getGenerateProductOptionsQuery($max_options);
        $query .= ' ) maxq on minq.product_id = maxq.product_id join ';
        $query .= '(' . 
                $this->getGenerateProductOptionsQuery($eq_options, 'id');
        $query .= ' ) eqq on maxq.product_id = eqq.product_id ';
        
        
        
        if(!empty($filters))
        {
             $query .= ' where '.implode(' and ', $option_not_null_checks).
             ' and '. implode(' and ', $filters). ' and ';
        }
        $query .= '(minq.product_id in (' . implode(',', $product_ids) . '))';
       
        
        $result = $this->vdo->fetchSingleArray($query);
        
        if(is_null($result)){
            return array();
        }
      //  */
        return array_unique($result);
	}
    
	public function setMinOptions($array)
	{
        if(!is_array($array) || is_null($array))
        {
            return false;
        }
        
        $this->options->min = $array;
        return true;
    }
        
    public function setEqOptions($array)
    {
        if(!is_array($array) || is_null($array))
        {
            return false;
        }
        
        $this->options->eq = $array;
        return true;
    }
        
    public function setMaxOptions($array)
    {
        
        if(!is_array($array) || is_null($array))
        {
            return false;
        }
        
        $this->options->max = $array;
        return true;
    }
	   
    private function handleVat() {
        $vatFunc = $this->GetVatfunc($this->getWid(),TRUE);
        $vat = Settings::get('vat_multiplyer',$this->getWid());
        $temp = array();
        foreach ($this->options->max as $key => $value) {
            if($key == $this->LOWEST_PRICE_STRING) {
                $value = $vatFunc($value,$vat);
            }
            $temp[$key] = $value;
        }
        $this->options->max = $temp;
        $temp = array();
        foreach ($this->options->min as $key => $value) {
            if($key == $this->LOWEST_PRICE_STRING) {
                $value = $vatFunc($value,$vat);
            }
            $temp[$key] = $value;
        }
        $this->options->min = $temp;
    } 
    
    private function getDisplayIdentifiers($tableAlias = null) 
    {
        $q =    'SELECT name
                 FROM result_display_identifiers';
                 
        $meta = VPDO::getVDO(DB_METADATA); 
        $list = $meta->getTableList($q);
        $result = array();
        
        foreach ($list as $entry) 
        {
            if($tableAlias == null) {
                $result[] = $entry->name;
            } else {
                $result[] = "$tableAlias." . $entry->name;
            }
        }
        
        return $result;
    }
    
    public function getOptions()
    {
        return $this->options;
    }
}
?>