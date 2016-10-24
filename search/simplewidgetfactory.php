<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}

class SimpleWidgetFactory extends WidgetFactory {
    
    function __construct($wid)
    {
        parent::__construct($wid);
        $this->vdo = VPDO::getVDO(DB_PREFIX.$this->getWid());       
    }
    
    function getGeneralWidgets(&$pids)
    {
        return null;
    }
    /*
    function getPriceSliderWidget(&$pids)
    {
        $extra_where = '';
        if(sizeof($pids) > 0){
            $extra_where = " where product_id IN (" .implode(", ", $pids) .")";
        }
        $sql = 'select CONVERT(lowest_price , DECIMAL(10,4)) as lowest_price from product_options '.$extra_where . ' ';
        $price_slider = new stdClass();
        $price_slider->id = 'slider';
        $this->fillSlider($price_slider, $sql, true, 'lowest_price');
       
        return $price_slider;
    }
    */
    function getCategoryWidget(&$pids)
    {
            
            $q = "SELECT id FROM options WHERE name = " . $this->vdo->quote('category'); 
            $catOptionId = $this->vdo->fetchOne($q);
            $q = 
            "SELECT 
               cats.name, cats.cid, cats.parent_id, count(cats.cid) as number_of_products
            FROM 
                options_values os 
            inner join 
                    options o  
                on 
                    o.id= os.option_id 
            inner join 
                    categories cats 
                on 
                    cats.cid = os.name 
            where 
                o.name = 'category_id'   ";
            if(sizeof($pids) > 0)
            {        
                $q .= ' and os.product_id IN ( '.implode(', ', $pids). ')';
            }
            $q .= ' GROUP BY cats.cid, cats.name'; 
        //  echo $q; exit;
            $cat_ids = $this->vdo->getTableList($q,'');
            if($cat_ids < 1) return null;
            $catset = new CategorySet($this->getWid());
            $catset->fillFromData($cat_ids);
            $tree = $catset->getTreeList();
            return $tree;
    }
        
}