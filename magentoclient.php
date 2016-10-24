<?

class MagentoClient implements ISoapCrawl {

    private $host;
    private $user = 'vubla';
    private $key;
    private $soap;
    private $session_id;
    private $wid;
    private $store_id = 0;
    public $errorState = false;
    function __construct($host, $wid){
    	
		if($wid < 1){
    		throw new Exception("Missing wid");
    	}
        $this->wid = $wid;
        $this->host = $host;
        // Magento login information 
        $this->key = Settings::get('mage_api_key', $wid); 
     
        list($this->soap,$this->session_id, $e) = self::getSoapClient($this->host, $this->key, $this->user);
        if(!is_null($e)){
            $this->errorState = Crawler::FAILED_MAGENTO_LOGIN;
        }
        if(VUBLA_DEBUG)
        {
            if($this->errorState){
                echo "\nFailed to connect\n";  
            } else {
                echo "\nConnection made\n";
            }
        } 
        $this->store_id = settings::get('mage_default_store_code',$wid);
        $this->root_category_id = settings::get('mage_root_category_id',$wid);
         
    }
    
    
    static function getSoapClient($host, $key, $user = 'vubla')
    {
        $success = false;
        $e = null;
        $session_id = null;
        $soap = null;
       
        
       
       
       
        try {
            $soap = new SoapClient('http://'. $host .'/api/?wsdl' );
            $session_id = $soap->login( $user, $key );
            $success = true;
        } catch (SoapFault $e) {
            if(VUBLA_DEBUG){
               // var_dump($e);
             }  
        }
        if(!$success)
        {
            try 
            {
                $soap = new SoapClient('http://'. $host .'/index.php/api/?wsdl' );
                $session_id = $soap->login( $user, $key );
                $success = true;
                $e = null;
            } catch (SoapFault $e) {
                if(VUBLA_DEBUG){
                //    var_dump($e);
                }  
            }
        }
          if(!$success)
        {
            try 
            {
                $soap = new SoapClient('http://'. $host .'/index.php/api/soap/?wsdl' );
                $session_id = $soap->login( $user, $key );
                $success = true;
                $e = null;
            } catch (SoapFault $e) {
                 if(VUBLA_DEBUG){
                   // var_dump($e);
                } 
            }
        }
        
      
        if(!$success)
        {
            try {
                $soap = new SoapClient(API_URL.'/soap/wsdl_trimmer.php?url=http://'. $host .'/api/?wsdl') ;
                $session_id = $soap->login( $user, $key);
                $e = null;
                $success = true;
            } catch (SoapFault $e) {
                VublaMailer::sendOnargiEmail('Failed Magento login', 'It failed for host '. $host .'<br /><br /><pre>'.ob_get_clean().'<br /><br />'.print_r($e,true).'</pre>');
                if(VUBLA_DEBUG){
                    var_dump($e);
                }
                
            }
        }
       if(!$success)
        {
            try {
                $soap = new SoapClient('http://api.vubla.com/soap/wsdl_trimmer.php?url=http://'. $host .'/api/?wsdl') ;
                $session_id = $soap->login( $user, $key ,array( "exceptions" => 1));
                
                $e = null;
            } catch (SoapFault $e) {
                VublaMailer::sendOnargiEmail('Failed Magento login', 'It failed for host '. $host .'<br /><br /><pre>'.ob_get_clean().'<br /><br />'.print_r($e,true).'</pre>');
                if(VUBLA_DEBUG){
                    var_dump($e);
                }
                
            }
        }
        return array($soap, $session_id, $e);
    }
     /**
     * This method retrieves a list of products.
     * 
     */
    function fetchProductIds(){
        
        $loop = true;
        $i = 0;
        while($loop && $i < 10)
        {
            try {
              
                $result = $this->soap->call ($this->session_id, 'catalog_product.list',array(0,$this->store_id));
                $loop = false;
            } catch (SoapFault $e){
               if(VUBLA_DEBUG) 
               {
                  var_dump($e);
               }
            }
            $i++;
        }
	
        $ids = array();
        foreach($result as $p){
            $ids[] = $p["product_id"];
        }
            
        return $ids;
    
    }
    
    /**
     * Traverse a tree and makes a list. 
     */
    function getCategories(){
        
         
         try {
            
            $cat_tree = $this->soap->call  ($this->session_id, 'catalog_category.tree', array($this->root_category_id,$this->store_id));
           
         } catch (SoapFault $e){
           if(VUBLA_DEBUG) 
           {
              var_dump($e);
           }
         
        }
         $root_categories_to_remove = array('Default Category', 'Root Catalog');
		 $toBeRemoved = array($this->root_category_id); 
		 $cat_tree["parent_id"] = 0;
         $soap = $this->soap;
         $session_id = $this->session_id;
         $func = function ($a ,$func) use (&$toBeRemoved, &$soap, $session_id, $root_categories_to_remove){
             		$cat = new Category();
					$cat->cid = $a["category_id"];
           			$cat->parent_id = $a["parent_id"];
            		$cat->name = $a["name"];  
					$acc = array();
									
				
						
                   
					if((isset($a["is_active"]) && $a["is_active"] == "1")|| in_array($a["name"], $root_categories_to_remove)) {
					    $loop =    true;
                        $i = 0;
                        while($loop && $i < 5)
                        {   
                            try{
                                $cat_info = $soap->call($session_id, 'catalog_category.info', $cat->cid); 
                               
                                if((isset($cat_info["include_in_menu"]) && $cat_info["include_in_menu"] == "1") || in_array($a["name"], $root_categories_to_remove)){
                                    $acc[] = $cat;
                                    if(is_array($a["children"])) {
                                        foreach ($a["children"] as  $value) {
                                            $acc = array_merge($acc,$func($value ,$func)); 
                                        }
                                    }   
                                }
                             
                                $loop = false;
                            } catch(SoapFault $e){
                                if(VUBLA_DEBUG)
                                {
                                  echo print_r($e, true);
                                }
                                
                            }
                            $i++;
                        }
					    	
					}
					
                    return $acc;
		 };
		
                   
         $cats = new CategorySet($this->wid );
         $cats->fillFromData($func($cat_tree,  $func));
	  /*
         foreach($cats as $key=>$cat){
        		
			$loop =	true;
			$i = 0;
			while($loop && $i < 5)
			{	
	        	try{
	         		$cat_info = $this->soap->call($this->session_id, 'catalog_category.info', $cat->cid); 
	       	 		if(!isset($cat_info["include_in_menu"]) || $cat_info["include_in_menu"] != "1"){
	       	 			$toBeRemoved[] = $cat->cid;	
	       	 		}
                 
					$loop = false;
			  	} catch(SoapFault $e){
			  	    if(VUBLA_DEBUG)
			  	    {
	           		  echo print_r($e, true);
                    }
	            	
	        	}
				$i++;
			}
		 }
		
	*/
		 $cats->removeCategories($toBeRemoved);
		
	     return $cats;
    }
    
    
    
    function getErrorState(){
        return $this->errorState;
    }
    
    function getXML($pid){
       
	    $loop =	true;
		$i = 0;
		while($loop && $i < 5)
		{
	        try {
	            $product_info = $this->soap->call  ($this->session_id, 'catalog_product.info', array($pid, $this->store_id));
				$loop = false;
	        } catch(SoapFault $e){
	            echo print_r($e, true) .' pid is: ' . $pid;
	            return null;
	        }
			$i++;
		}
   
        if( !isset($product_info['status']) || $product_info['status'] != 1 || 
            !isset($product_info["price"]) || is_null($product_info["price"]) ||
            !isset($product_info['visibility']) ||  $product_info['visibility'] < 3
            
            ){
          
            return null;
            
        }
        
        if(settings::get('hide_products_out_of_stock',$this->wid))
        {
        
            $loop =    true;
            $i = 0;
            while($loop && $i < 5)
            {
                try {
                    $stock_info = $this->soap->call  ($this->session_id, 'cataloginventory_stock_item.list', $pid);
                    $loop = false;
                } catch(SoapFault $e){
                    echo print_r($e, true) .' pid is: ' . $pid;
                    return null;
                }
                $i++;
            }    

            if(!isset($stock_info[0]) || !isset($stock_info[0]['is_in_stock']) || $stock_info[0]['is_in_stock'] != 1 ){
            // echo print_r($stock_info, true) .' pid is: ' . $pid;
                return null;

            }

            $product_info['is_in_stock'] = $stock_info[0]['is_in_stock'];
        }
        
        $images = $this->soap->call($this->session_id,'catalog_product_attribute_media.list', $pid);
        
        
        if(!self::is_in_discount_periode($product_info))
        {
            $product_info['special_price'] = null;
        }
        
        
        
        $xml = array();
        foreach($images as $image ){
            @$xml['image_link'] =  $image['url'];   
            /// We set the url for each picture
        
            if(isset($image['types']) && in_array('thumbnail', $image['types'])){
                /// If this image is a thumbnail(There can only be one thumbnail), then we skip the rest
                break;     
            }
        }
        
        
      
        foreach($product_info as $key=>$val){
            
            
            $key = $this->translateXml($key);
            $xml[$key] = $val;
          
        }
     	$xml['buy_link'] = 'http://'.$this->host.'/index.php/checkout/cart/add?qty=1&product='.$pid;
        
        return $xml;
    
    }
    
    public static function is_in_discount_periode($product_info){
  
        if( (is_null($product_info['special_to_date']) ||   $product_info['special_to_date'] > date('Y-m-d 00:00:00',time())) &&
            (is_null($product_info['special_from_date']) || $product_info['special_from_date'] <= date('Y-m-d 00:00:00',time())))
        {
             return true;
        } else {
            return false;
        }
       
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


}




