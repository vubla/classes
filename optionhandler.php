<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}




class OptionHandler extends SetHandler {
    
    static $facet_types = array('slider', 'checkboxeslist');
    static $r_display_identifiers = array('price', 'name', 'description', 'discount_price', 'image_link', 'link', 'buy_link', 'lowest_price', 'quantity','sku');
    static $sortable_as = array('not', 'string', 'number');
    
    
    public static function createOptionsSettingSet($wid, $data = null){
        return parent::createSet($wid, $data,'OptionsSettingSet');
    }
    
    /**
     * Lazy is not implemented. 
     * Takes a wid and a name and retrieves the option setting from the database. 
     */
    public static function getOptionSetting($wid,$name,$lazy = false) {
        $db = VPDO::getVdo(DB_PREFIX . $wid);
        if($lazy) {
            //Not implemented
        } else {
            $q = 'SELECT * FROM `options_settings` WHERE r_display_identifier = ?';
            $temp = $db->getRow($q,array($name));
            if(!$temp) 
            {
                throw new VublaException("The Query " . $q . " (? = ".$name.") did not return anything");    
            }
            $result = new OptionsSetting();
            $result->name = $temp->name; 
            $result->importancy = $temp->importancy; 
            $result->sortable = $temp->sortable;   
            $result->r_display_identifier = $temp->r_display_identifier;    
            $result->facet_type = $temp->facet_type;
            return $result;
        }
    }
    
    /**
     * Returns the possuble sorting options
     */
    public static function getSortableOptions($wid){
       
        $q = "  SELECT r_display_identifier as name, order_by 
                FROM  `options_settings` os   join ((select 'asc' as order_by) union (select 'desc' as order_by)) as a
                WHERE os.sortable !=  'not'
                AND os.r_display_identifier !=  '' ";
        $db = VPDO::getVdo(DB_PREFIX . $wid);
        return $db->getTableList($q);
    }    
   
   
    /**
     * Creates an options setting from a name and the int. 
     */
    public static function createOptionsSetting($name, $number_of_values){
        $product_names = array('name', 'title', 'navn', 'model');
        $category_names = array('categories', 'category', 'kategori', 'kategorier');
        $price_names    = array('price', 'pris');
        $discount_price_names    = array('discount_price','special_price', 'rabat');
        $description_names    = array('description','beskrivelse');
        $manufacturer_names = array('supplier', 'producter', 'producent');
        $link_names = array('url', 'link');
        $buy_link_names = array('buy_link');
        $image_link_names = array('image_link','image');
        $brand_names = array('brand', 'brands');
        $quantity_names = array('quantity',  'products_quantity', 'product_quantity', 'is_in_stock');
       // $image_label_name = array('image_label');
        $r_display_identifier = false;
        $importancy = 0;
        $facet_type = '';
        $sortable = 'not';
       
        if(self::_isIn($product_names, $name) && $number_of_values == 1){   
            $importancy = 3;
           // $sortable = 'string';
            $r_display_identifier    = 'name';
            $facet_type = '';
        }   
      
        if(self::_isIn($category_names, $name) && strpos($name, 'aw_os_') === false && strpos($name, '_id') === false){
            $importancy = 2;
           // $sortable = 'not';
            $r_display_identifier    = false;
            $facet_type = '';
        }
        if(self::_isIn($price_names, $name) && $number_of_values == 1){
            $importancy = 0;
           // $sortable = 'not';
            $r_display_identifier    = 'price';
            $facet_type = '';
        }  
        if(self::_isIn($manufacturer_names, $name) && $number_of_values == 1){
            $importancy = 2;
         //   $sortable = 'string';
            $r_display_identifier    = false;
            $facet_type = '';
        }     
        if(self::_isIn($description_names, $name)){
            $importancy = 1;
        //    $sortable = 'not';
            $r_display_identifier    = 'description';
            $facet_type = '';
            } 
   
        if(self::_isIn($link_names, $name) && $number_of_values == 1){
            $importancy = 0;
            $sortable = 'not';
            $r_display_identifier    = 'link';
            $facet_type = '';
        } 
        if(self::_isIn($buy_link_names,$name) && $number_of_values == 1){
            $importancy = 0;
            $sortable = 'not';
            $r_display_identifier    = 'buy_link';
            $facet_type = '';
        } 
        if(self::_isIn( $image_link_names, $name)){
            $importancy = 0;
            $sortable = 'not';
            $r_display_identifier    = 'image_link';
            $facet_type = '';
        }
        if(self::_isIn(    $discount_price_names, $name)){
            $importancy = 0;
            $sortable = 'not';
            $r_display_identifier    = 'discount_price';
            $facet_type = '';
        } 
         if(self::_isIn(    $quantity_names, $name)){
            $importancy = 0;
            $sortable = 'not';
            $r_display_identifier    = 'quantity';
            $facet_type = '';
        } 
        if(self::_isIn(    $brand_names, $name)){
            $importancy = 2;
           
        } 
        if($name == 'sku' && $number_of_values == 1){   
            $importancy = 3;
            $r_display_identifier = 'sku';
        //    $facet_type = 'slider';
        } 
        if($name == 'lowest_price' && $number_of_values == 1){   
            $importancy = 0;
            $sortable = 'number';
            $r_display_identifier    = 'lowest_price';
        //    $facet_type = 'slider';
        } 
         if($name == 'pid' && $number_of_values == 1){   
            $importancy = 0;
        //    $sortable = 'not';
            $r_display_identifier    = 'pid';
         //   $facet_type = '';
        }    
        if($name == 'image_label'){
            $importancy = 1;
            $sortable = 'not';
          
          
        } 
        if(strpos($name, 'meta_') !== false && $importancy > 1)
        {
            $importancy--;
        }
        if($number_of_values > 1 && $r_display_identifier  != false){
            $facet_type = 'combobox';
            $importancy = ($importancy > 0) ? $importancy : 1;
        } 
        
        $metaSetting = new  OptionsSetting(); 
        $metaSetting->name = $name;
        $metaSetting->importancy = $importancy;
        $metaSetting->sortable = $sortable;
        $metaSetting->facet_type = $facet_type;
        $metaSetting->r_display_identifier = $r_display_identifier;
        return $metaSetting;    
    }


    /**
     * Used to correct the options_settings table.
     */
    public static function correctOptionsSettings($wid){
       
        $db = VPDO::getVdo(DB_PREFIX . $wid);
        
        $sql = 'DELETE FROM options_settings where name not in (select name from options'.ScrapeMode::getPF() .') ';
        $db->exec($sql);
        
        
        if($db->fetchOne('select count(*) from options_settings') < 1){
            return false;
        }
        $sql = "SELECT 
                    * 
                FROM (
                    SELECT 
                        COUNT( r_display_identifier ) AS c, name, r_display_identifier
                    FROM 
                        options_settings
                    WHERE 
                        r_display_identifier !=  ''
                    AND 
                        r_display_identifier IS NOT NULL 
                    GROUP BY 
                    r_display_identifier
                    ) 
                AS i 
                WHERE i.c > 1";
        $stm = $db->prepare($sql);
        $stm->execute();
        $stm->setFetchMode( PDO::FETCH_CLASS, 'stdClass');
        $all = $stm->fetchAll();
        $stm->closeCursor();
        foreach ($all as $a) {
            $sql = "select name from options_settings where r_display_identifier = ?";
            $stm = $db->prepare($sql);
            $stm->execute(array($a->r_display_identifier));
            $names = $stm->fetchAll();
            $stm->closeCursor();
            $best_choice = self::findBestChoice( $names , $a->r_display_identifier);
            $sql = "update  options_settings set r_display_identifier = '' where r_display_identifier = ? and name != ?";
            $stm = $db->prepare($sql); 
            $stm->execute(array($a->r_display_identifier,$best_choice));
            $stm->closeCursor();
        }
        $options_settings_to_update = array( array('string','', 'name'));
        foreach($options_settings_to_update as $opt){
	        $sql = 'update  options_settings set sortable = ?, facet_type = ? where r_display_identifier = ?';
	        $stm = $db->prepare($sql); 
	        $stm->execute($opt);
            $stm->closeCursor();
        } 
        
        return true;
        
    }

        
    private static function findBestChoice($names,  $r_display_identifier){
        switch($r_display_identifier){
            // Most important first!!!
            case 'image_link':
                $precedence = array('image', 'image_link');
                break;
            case 'link':
                $precedence = array('url','products_url', 'product_url','product_link', 'link');
                break;
            case 'name':
                $precedence = array('name', 'product_name','products_name', 'title', 'product_title','products_title', 'model', 'products_model', 'product_model');
                break;
            case 'price':
                $precedence = array('price', 'products_price','product_price');
                break;
            case 'discount_price':
                $precedence = array('discount_price', 'special_price', 'offer');
                break;
            case 'description':
                $precedence = array('short_description','product_description' ,'description', 'meta_description');
                break;
            case 'pid':
                $precedence = array('pid', 'id','products_id', 'product_id');
                break;
            case 'quantity':
                $precedence = array('quantity',  'products_quantity', 'product_quantity', 'is_in_stock');
                break;
            case 'lowest_price':
                $precedence = array('lowest_price');
                break;
            default:
                $precedence = array(); 
        }
        $count = 0;
        
        foreach ($names as  $name) {
            
            $result[$name['name']] = 0;
        }
        $precedence = array_reverse($precedence);
        foreach ($precedence as $value) {
            $count = $count + 1; 
        
            foreach ($names as  $name) {
                if($value == $name['name']){
                    $result[$name['name']] = $count;   
                }      
            }
        }
       

       asort($result, SORT_NUMERIC);
       $key = array_keys($result);
       
       $key_to_save = array_pop($key);
       return $key_to_save;  
    }


    private static function _isIn($array,$name){
            return preg_match('#\b.*(?:'.implode('|',$array).').*\b#i', $name);
     }
       
}






?>