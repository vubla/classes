<?php 

checkConfig();


class MagentoFetcher extends SoapFetcher {
    
    protected $root_category_id = 1;
    protected $store_id = 0;
      protected $getAttributeSupported = true;
    
    
    public function __construct($wid){
        $this->store_id = settings::get('mage_default_store_code',$wid);
        $this->root_category_id = settings::get('mage_root_category_id',$wid);
        try {
            parent::__construct($wid);
        } catch(ScrapeException $e){
            throw $e;
        }
    }
    
    /**
     *
     * @var CategorySet 
     */
    private $catset;
    
    public function getNextCategory()
    {
        if(ScrapeMode::get() == 'update')
        {
            return null;
        }
        if(is_null($this->catset)){
             $this->catset = $this->getCategories();
             if(is_object($this->catset) && get_class($this->catset) == 'CategorySet'){
                $this->catset->removeCategories(array('Default Category', 'Root Catalog'));
            }
            
        }
          
        if(is_null($this->catset) || $this->getCategoriesLeft() == 0){
            return null;
        }

        return $this->catset->shift();
    }

    
    public function getCategoriesLeft(){
        parent::setCategoriesLeft($this->catset->length());
        return parent::getCategoriesLeft();
    }
    
     public function getProductsLeft(){
        parent::setProductsLeft(sizeof($this->ids));
        return parent::getProductsLeft();
    }
    
    protected function getClient() {
        if(is_null($this->client)){
             $this->client = MagentoSoapClientFactory::getFactory($this->wid)->create();
        }
        
        return $this->client;
    }
    
    
    
    private function getCategories() {
        
            
         $cat_tree = $this->client->call  ( 'catalog_category.tree', array($this->root_category_id,$this->store_id));
     
         if(is_null($cat_tree)) return null;
       
         $root_categories_to_remove = array('Default Category', 'Root Catalog');
         $toBeRemoved = array($this->root_category_id); 
         $cat_tree["parent_id"] = 0;
         $soap = $this->client;
         $func = function ($a ,$func) use (&$toBeRemoved, &$soap,$root_categories_to_remove){
            $cat = new Category();
            $cat->cid = $a["category_id"];
            $cat->parent_id = $a["parent_id"];
            $cat->is_active = $a["is_active"];
            $cat->name = $a["name"];  
          //  var_dump($a);
            $acc = array();
                                    
            //if((isset($a["is_active"]) && $a["is_active"] == "1")|| in_array($a["name"], $root_categories_to_remove))
             {
              

                           // $cat_info = $soap->call('catalog_category.info', $cat->cid); 
                          //   var_dump($cat_info);
                          //  if((isset($cat_info["include_in_menu"]) && $cat_info["include_in_menu"] == "1") || in_array($a["name"], $root_categories_to_remove))
                            {
                                $acc[] = $cat;
                                if(is_array($a["children"])) {
                                    foreach ($a["children"] as  $value) {
                                        $acc = array_merge($acc,$func($value ,$func)); 
                                    }
                                }   
                            }
                        }           
                    return $acc;
         };
        
         //var_dump($acc);        
         $cats = new CategorySet($this->wid );
         $cats->fillFromData($func($cat_tree,  $func));
         
         $cats->removeCategories($toBeRemoved);
         return $cats;
    }

    
    
    /**
     * Returns an array of products, all arrays. All products should be saved.
     * Return null on failure;
     * @param int $pid
     * @return null|array 
     */
    protected function getProduct($pid) {
        $this->verboseOut('Getting product with pid: '.$pid);
		
    	
        $product_info = $this->client->call  ('catalog_product.info', array($pid,  $this->store_id));
    
        $expectations = array();
        if(is_null($product_info)) 
        {
            $expectations[] = 'No product info (NULL)';
        }
        else 
        {
            if(!isset($product_info['status']) ) $expectations[] = 'No status';
            if($product_info['status'] != 1 ) $expectations[] = 'Status not 1';
            if(!isset($product_info["price"]) || is_null($product_info["price"]) ) 
            {
                $type = 'none';
                if(!is_null($product_info["type_id"]))
                {
                    $type = $product_info["type_id"];
                }
                if($type == 'bundle' || $type == 'grouped')
                {
                    $product_info["price"] = -1;
                }
                else 
                {
                    $expectations[] = 'No price for type: '.$type;
                }
            }
            if(!isset($product_info['visibility']) ) $expectations[] = 'No visibility';
            if($product_info['visibility'] < 3) $expectations[] = 'Visibility less than 3';
        }
        if(!empty($expectations))
        {
            $this->verboseOut('Product with pid: '.$pid.' failed due to the following: '.implode(', ', $expectations));
            return null;
        }
        
        
        if(settings::get('hide_products_out_of_stock',$this->wid))
        {
          
            $stock_info = $this->client->call  ('cataloginventory_stock_item.list', $pid);
            if(is_null($stock_info) || !isset($stock_info[0]) || !isset($stock_info[0]['is_in_stock']) || $stock_info[0]['is_in_stock'] != 1 ){
            
                return null;
            }
            $product_info['is_in_stock'] = $stock_info[0]['is_in_stock'];
        }
        
        
        if(!self::is_in_discount_periode($product_info,Settings::get('mage_ignore_discount_expire_date', $this->wid)))
        {
            $product_info['special_price'] = null;
        }
        
       
        /*
        $images = $this->client->call('catalog_product_attribute_media.list', $pid);
        $xml = array();
        if(is_array($images))
        {
            foreach($images as $image ){
                @$xml['image_link'] =  $image['url'];   
                /// We set the url for each picture
            
                if(isset($image['types']) && in_array('thumbnail', $image['types'])){
                    /// If this image is a thumbnail(There can only be one thumbnail), then we skip the rest
                    break;     
                }
            }
        }
        */
        
        $skippable_attributes = array('price', 'product_name');
        
        foreach($product_info as $key=>$val){
           
            if(!is_array($val) && $this->getAttributeSupported && !in_array(strtolower($key),$skippable_attributes))
            {
                try 
                {
                    $val = MagentoAttributeSet::get( $this->client,$product_info['type_id'], $product_info['set'])->getAttribute($key)->getOptionLabel($val);
                }
                catch(UnsupportedFeatureException $e)
                {
                    $this->getAttributeSupported = false;
                }
            } 
            $key = $this->translateXml($key);
            $xml[$key] = $val;
        }
        
        //$xml['buy_link'] = 'http://'.$this->getHostname().'/index.php/checkout/cart/add?qty=1&product='.$pid;
        
        return $xml;
        
    }
     private function translateXml($key){
    
        switch ($key){
            case 'url_path':
                $key = 'url';
                break;
            case 'special_price':
                $key = 'discount_price';
                break;
            case 'name':
                $key = 'product_name';
                break;
            case 'price':
                $key = 'product_price';
                break; 
             case 'description':
                $key = 'product_description';
                break;
            case 'product_id':
                $key = 'pid';
                break;
        }
        
        return $key;
    } 
     
  public static function is_in_discount_periode($product_info, $ignore_to_date = false)
  {
        $to_okay   = $ignore_to_date || (!isset($product_info['special_to_date']))   || is_null($product_info['special_to_date'])   || $product_info['special_to_date'] > date('Y-m-d 00:00:00',time());
        $from_okay = (!isset($product_info['special_from_date'])) || is_null($product_info['special_from_date']) || $product_info['special_from_date'] <= date('Y-m-d 00:00:00',time());
        $price_okay = array_key_exists('special_price', $product_info) && ((float)$product_info["price"]) > ((float)$product_info["special_price"]);
        if($to_okay && $from_okay && $price_okay)
        {
            return true;
        } else {
            return false;
        }
       
    }

   private function translateKeys($key){
    
        switch ($key){
            case 'url_path':
                $key = 'url';
                break;
            case 'special_price':
                $key = 'discount_price';
                break;
            case 'name':
                $key = 'product_name';
                break;
            case 'price':
                $key = 'product_price';
                break;
             case 'description':
                $key = 'product_description';
                break;
            case 'product_id':
                $key = 'pid';
                break;
        }
        
        return $key;
    }
  

    
     protected function fetchProductIds() {
     	$filter = array();
        if(ScrapeMode::get() == 'update')
        {
            $time =  $this->mpdo->fetchOne('select last_updated from crawllist where wid = '.(int) $this->wid);
            $filter['updated_at']  = array('from'=>date('Y-m-d G:i:s', $time - 36000));  //
            //$filter = array('updated_at'=>array('from'=>date('Y-m-d G:i:s', $time - 36000)));  
           
        } 
		if(settings::get('mage_soap_fetch_only_active',$this->wid))
		{
			$filter['status']  = array('like'=>'1');
	    }
		if(empty($filter)) $filter = null;
		
        $result = $this->getClient()->call ('catalog_product.list',array($filter,$this->store_id));
        VOB::_n('Found ' .sizeof($result) . ' products');
        $ids = array();
        foreach($result as $p){
            $ids[] = $p["product_id"];
        }
            
        return $ids; 
    }
}
