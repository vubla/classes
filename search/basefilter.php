<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}

abstract class BaseFilter extends ProductIdHandler
{
    protected $vdo;
    protected $log;
    protected $query;
    private $fullSearch;
    
    private $LOWEST_PRICE_STRING = 'lowest_price';
    
    function __construct($wid, $fullSearch = false)
    {
        $this->fullSearch = $fullSearch;
        parent::__construct($wid);
        $this->vdo = VPDO::getVdo(DB_PREFIX . $this->getWid());
    }
    
    abstract protected function filter(array $product_ids);
    
    function getResults(array $product_ids)
    {
        $result = $this->filter($product_ids);
        if($this->fullSearch) // We dont want to sort the result if it is a full search
        {
            return $result;
        }
        
        return $this->maintainOrder($product_ids, $result);
    }
    
    function getFullSearch()
    {
        return $this->fullSearch;
    }

    public function getLowestPriceString() 
    {
        return $this->LOWEST_PRICE_STRING;
    } 
    
    protected function generateWhereClauseFromProductIds(array $product_ids, $tablename = '',$columname = '`product_id`')
    {
        if(empty($product_ids))
        {
            return '';
        }
        return '('.($tablename == '' ? '' : $tablename . '.').$columname . ' = ' . implode(' or ' . ($tablename == '' ? '' : $tablename . '.').$columname . ' = ', $product_ids).')';
    }
    
    protected function isOption($option)
    {
        if(is_null($option)){
            return false;
        }
        $q = 'SELECT id FROM options WHERE name = ?';
        $res = $this->vdo->fetchOne($q,array($option));
        return $option == $this->getLowestPriceString() || isset($res);
    } 
    
    protected function getRegularPriceName() 
    {
        $q = 'SELECT name FROM options_settings WHERE r_display_identifier = ?';
        $res = $this->vdo->fetchOne($q,array('price'));
        return $res;
    }
    
    protected function getDiscountPriceName() 
    {
        $q = 'SELECT name FROM options_settings WHERE r_display_identifier = ?';
        $res = $this->vdo->fetchOne($q,array('discount_price'));
        return $res;
    }
    
    public function getGenerateProductOptionsQuery($options, $groupby = 'product_id') 
    {
        if(!is_array($options))
        {
            return false; 
        }
    
    
        $result = "SELECT `options_values`.`product_id` AS `id`,`options_values`.`product_id` AS `product_id`";
        //$priceFound = false;
        
        $additionalJoins = array();
        foreach ($options as $value) 
        {
            if(!is_object($value))
            {
                continue;   
            }
        
            $name = $value->name; 
            /*if($value->name == $this->getLowestPriceString()) {
                $discountPriceName = new stdClass();
                $discountPriceName->name = $this->getDiscountPriceName();
                $discountPriceName->aggregator = $value->aggregator;
                $discountPriceName->type = 'decimal(20,2)';
    
                $regularPriceName = new stdClass();
                $regularPriceName->name = $this->getRegularPriceName();
                $regularPriceName->aggregator = $value->aggregator;
                $regularPriceName->type = 'decimal(20,2)';
                $result .= ", ((case when (`temp_table_".sizeof($additionalJoins)."`.`$value->aggregator"."_$discountPriceName->name` IS NULL) then CAST(`temp_table_".sizeof($additionalJoins)."`.`$value->aggregator"."_$regularPriceName->name` AS $value->type) else CAST(`temp_table_".sizeof($additionalJoins)."`.`$value->aggregator"."_$discountPriceName->name` AS $value->type) end)) AS `$value->aggregator"."_$value->name`";
                $additionalJoins[] = ' JOIN ('.$this->getGenerateProductOptionsQuery(array($discountPriceName,$regularPriceName)).') AS temp_table_'.sizeof($additionalJoins).' on `temp_table_'.sizeof($additionalJoins).'`.`product_id` = `options_values`.`product_id` ';
            } else*/ if(isset($value->type)) {
                $result .= ", $value->aggregator((case when (`options`.`name` = '$name') then CAST(`options_values`.`name` AS $value->type) else NULL end)) AS `$value->aggregator"."_$value->name`";
            } else {
                $result .= ", $value->aggregator((case when (`options`.`name` = '$name') then `options_values`.`name` else NULL end)) AS `$value->aggregator"."_$value->name`";
            }
        }
        

        $result .= " FROM `options` 
        JOIN `options_values` on (`options`.`id` = `options_values`.`option_id`)";
        if(!empty($additionalJoins)) 
        {
            foreach ($additionalJoins as $value) 
            {
                $result .= $value;

            }
        }

        $result .= " group by `options_values`.`".$groupby."`";
        return $result;
    }

    protected function maintainOrder($orderedArray,$unorderedArray)
    {
        $result = array();
        $tempArray = array();
        $orderedSize = sizeof($orderedArray);
        $unorderedSize = sizeof($unorderedArray);
        foreach ($unorderedArray as $value) {
            $tempArray[$value] = $value;
        }
       
        for($i = 0, $j = 0 ; $i < $orderedSize && $j < $unorderedSize ; $i++)
        {
            if(isset($tempArray[$orderedArray[$i]]))
            {
                $result[] = $orderedArray[$i];
                $j++;
            }
        }
        
        if(sizeof($result) != sizeof($unorderedArray))
        {
            throw new VublaException('Entry in unordered array found in ordered array');
        }
        return $result;
    }
}
?>