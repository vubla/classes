<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}

class WidgetFactory extends ProductIdHandler {
    
    function __construct($wid)
    {
        parent::__construct($wid);
        $this->vdo = VPDO::getVDO(DB_PREFIX.$this->getWid());       
    }

    function getResults( array $pids)
	{
	    if(!$pids) $pids = array();  
		$widgets = array();
      
         if($general = $this->getGeneralWidgets($pids))
        {
            $widgets = $general;
        }
        if($slider = $this->getPriceSliderWidget($pids))
        {
            $widgets['price_slider'] = $slider;
        }
        if($categories = $this->getCategoryWidget($pids))
        {
            $widgets['category_three'] = $categories;
        }
       
        return $widgets;
	}
    
    function getGeneralWidgets(&$pids)
    {
        /// Select the various facets
        $sql = "select o.id, o.name, facet_type from options o inner join options_settings os on os.name = o.name".
                " where (facet_type != '') and r_display_identifier != 'lowest_price'";
        $stm = $this->vdo->prepare($sql);
        $stm -> execute();
        $widgets = array();
        
        while($opset = $stm->fetchObject()){
           
            if(!in_array($opset->facet_type, OptionHandler::$facet_types)){
                continue;   
            }
            /// Generate the right class
            $widget_class_name = ucfirst($opset->facet_type) . 'Widget';
           // $widget = new $widget_class_name($this->getWid(), ucfirst($opset->name));
            $widget = Widget::create($this->getWid(),$opset->facet_type,$opset->name);
            /// Get the options for each facet according to the products in search
            
            $extra_where = '';
            if(sizeof($pids) > 0){
                $extra_where = " and (product_id = " .implode(" or product_id = ", $pids) .")";
            }
            $q = "select name from options_values where option_id = ".$opset->id." ".$extra_where." group by name";
            
            
            
            if($widget instanceof SliderWidget)
            {
                
                $this->fillSlider($widget, $q);
                if($widget->min >= 0 && $widget->max >= 0)
                {
                    $widgets[$opset->name] = $widget;   
                }
            } 
            else if ($widget instanceof ListWidget)
            {
                $this->fillList($widget, $q);
                if(($widget->options && !empty($widget->options)))
                {
                    $widgets[$opset->name] = $widget;   
                }
                
            } 
            else 
            {
                continue;
            }
            
            /// make sure we only save the usefull ones
         
                
        }
        $stm->closeCursor();
        if(sizeof($widgets) < 1) return null;
        return $widgets;
        
    }


    
    function fillSlider(&$widget, $q, $consider_vat = false, $field = 'name'){
        
        $res = $this->vdo->getTableList($q. ' order by '.$field.' asc');
        
        if(is_null($res)){
            
          //  var_dump($q.' order by '.$field.' asc'); exit;
        }
     
    
        $min_identifier = 'min_'.$widget->id;
        $max_identifier = 'max_'.$widget->id;
        /* 
         * It is neacssary to have variables here because the variables are generated in html and passed through the get methods.
         */
        
        if(isset($this->min_options[$widget->id]) && isset($this->max_options[$widget->id]))
        {
             $widget->selected_min = floor($this->min_options[$widget->id]);
             $widget->selected_max = ceil($this->max_options[$widget->id]);
             
        }     
        // var_dump($res);exit;
        if(is_null($res))
        {
            return null;
        }
        if(sizeof($res) == 1)
        {
            $min = $max = $res[0]->$field;
        }
        else
        {
            
            $min = array_shift($res)->$field;
            $max = array_pop($res)->$field;
        }
       
        if($consider_vat){
            $vat_multiplyer =    Settings::get('vat_multiplyer', $this->getWid());
            $vat_func = $this->GetVatfunc($this->getWid());
            $widget->min = floor($vat_func($min,$vat_multiplyer));
            $widget->max = ceil($vat_func($max,$vat_multiplyer));
        } else {
            $widget->min = floor($min);
            $widget->max = ceil($max);
        }
          
           
        if($widget->min > $widget->selected_min){
           $widget->selected_min = $widget->min;
        }
        if($widget->max < $widget->selected_max){
           $widget->selected_max = $widget->max;
        }
      
        /*
        if($this->previous != $this->q){
           $_GET['min_price'] = $price_slider->selected_min_price = $price_slider->min_price; 
           $_GET['max_price'] = $price_slider->selected_max_price = $price_slider->max_price;
           // This is a little haxy, but i can't come op with a smarter solution right now
           // A thourough analysis and description of the slider must be done. 
        }*/
      //  return $widget;
    }
    
    function fillList(&$widget, $q){
        
        $stm2 = $this->vdo->prepare($q);
        $stm2 ->execute();

        $temp = $stm2->fetchAll();
        foreach ($temp as  $value) {
             $widget->options[] = $value[0];
        }
    }
    
    function getPriceSliderWidget(&$pids)
    {
      
        
         $extra_where = '';
            if(sizeof($pids) > 0){
                $extra_where = " where (product_id = " .implode(" or product_id = ", $pids) .")";
            }
        $sql = 'select CONVERT(lowest_price , DECIMAL(10,4)) as lowest_price from product_options '.$extra_where . ' ';
        $price_slider = Widget::create($this->getWid(), 'slider', 'lowest_price');
        $this->fillSlider($price_slider, $sql, true, 'lowest_price');
        /*
        $res = $this->vdo->getTableList($sql);
        
       
       
        if(!is_null($this->min_price) && !is_null($this->max_price))
        {
             $price_slider->selected_min_price = floor($this->min_price);
             $price_slider->selected_max_price = ceil($this->max_price);
             
        }     
        $max = array_shift($res)->lowest_price;
        $min = array_pop($res)->lowest_price;

        $vat_multiplyer =    Settings::get('vat_multiplyer', $this->getWid());
        $vat_func = $this->GetVatfunc($this->getWid());
        $price_slider->min_price = floor($vat_func($min,$vat_multiplyer));
        $price_slider->max_price = ceil($vat_func($max,$vat_multiplyer));
     
           
         
        if($price_slider->min_price > $price_slider->selected_min_price){
           $price_slider->selected_min_price = $price_slider->min_price;
        }
        if($price_slider->max_price < $price_slider->selected_max_price){
           $price_slider->selected_max_price = $price_slider->max_price;
        }
        /*
        if($this->previous != $this->q){
           $_GET['min_price'] = $price_slider->selected_min_price = $price_slider->min_price; 
           $_GET['max_price'] = $price_slider->selected_max_price = $price_slider->max_price;
           // This is a little haxy, but i can't come op with a smarter solution right now
           // A thourough analysis and description of the slider must be done. 
        }*/
       
       
        return $price_slider;
    }
    
    function getCategoryWidget(&$pids)
    {
            
            $q = "SELECT id FROM options WHERE name = " . $this->vdo->quote('category'); 
            $catOptionId = $this->vdo->fetchOne($q);
            $wheres = array();
            foreach($pids as $id) {
                $wheres[] = ' os.product_id = '.$this->vdo->quote($id) .' ';
            }  
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
                $q .= ' and ( '.implode(' or ', $wheres). ')';
            }
            $q .= ' GROUP BY cats.cid, cats.name'; 
		//	echo $q; exit;
            $cat_ids = $this->vdo->getTableList($q,'');
         if($cat_ids < 1) return null;
         $category_tree = Widget::create($this->getWid(), 'category', 'categories');
         $category_tree->categories = $cat_ids; 
         return $category_tree;
    }
    
    //These guys are depricated: 
    /*
    private function getDiscountPriceName() {
        //return 'discount_price';
        $q = 'SELECT name FROM options_settings WHERE r_display_identifier = ?';
        $res = $this->vdo->fetchOne($q,array('discount_price'));
        return $res;
    }
     
    private function getRegularPriceName() {
        //return 'discount_price';
        $q = 'SELECT name FROM options_settings WHERE r_display_identifier = ?';
        $res = $this->vdo->fetchOne($q,array('discount_price'));
        return $res;
    }
     */
     
     
    
}
