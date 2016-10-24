<?php 

checkConfig();


class XmlFetcher extends CatalogFetcher {
    
    public $catalog;
    
    
    
    public function getNextCategory() 
    {
        if(is_null($this->catalog)){
            $this->loadCatalog();
        }
      
        $cat = null;
        if(isset($this->catalog['category'])){
        
            $a = array_shift($this->catalog['category']);
            $cat = new Category();
            if(!is_array($a))
            {
                return null;
            }
            $cat->cid = $a["id"];
            $cat->parent_id = $a["parent_id"];
            $cat->name = $a["name"];  
            if(isset($a["description"])){
                $cat->description = $a["description"]; 
            }
           
        }     
        return $cat;
    }
    
    public function getNextProduct() 
    {
        if(is_null($this->catalog)){
            $this->loadCatalog();
        }
       
        if(!$this->getProductsLeft()){
            return null;
        }
        
        try {
            $product = $this->createProduct(array_shift($this->catalog['product']));
        } catch (VublaException $e)
        {
            Scraper::$errors[] = $e;
            
        }
        return $product;        
       
    }
    
    public function getCategoriesLeft(){
        parent::setCategoriesLeft(sizeof($this->catalog['category']));
        return parent::getCategoriesLeft();
    }
    
    public function getProductsLeft(){
        parent::setProductsLeft(sizeof($this->catalog['product']));
        return parent::getProductsLeft();
   
    }
    
    protected function loadCatalog(){
        try {
            $vublafile =   Settings::get('xml_output_location',$this->wid);
            $http_username = Settings::get('http_username', $this->wid);
            $context = null;
            if($http_username)
            {
                $http_password = Settings::get('http_password', $this->wid);
                $context = stream_context_create(array(
                    'http' => array(
                        'header'  => "Authorization: Basic " . base64_encode("$http_username:$http_password")
                    )
                ));
            }
            $parser = new VublaXmlParser($this->hostname. $vublafile, $context);
            $parser->setFromEncoding( Settings::get('encode_from', $this->wid));
            $this->catalog = $parser->getCatalog();
        } catch(NoScrapeFileException $e)
        {
            if(is_null($context)){
               @ $test_content = file_get_contents('http://'.$this->hostname);
            } else {
                @ $test_content = file_get_contents('http://'.$this->hostname, false, $context);
            }
            
            if(!$test_content)  // we ignore the error and jus test the result
            {
                throw new NoConnectionException('With hostname http://'. $this->hostname . ' and wid '.$this->wid );
            }
            throw $e;
        }
    }
}
