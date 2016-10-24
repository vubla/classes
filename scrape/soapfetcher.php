<?php 

checkConfig();


abstract class SoapFetcher extends CatalogFetcher {
    
    /**
     *
     * @var VublaSoapClient 
     */
    protected $client;
    
    /**
     *
     * @var Array[int] 
     */
    protected $ids = null;
    
    function __construct($wid) {
        parent::__construct($wid);
        try 
        {
            $this->client =  $this->getClient();
        } catch(MagentoLoginException $e){
            
            throw $e;
        }
        
       
    }
    
    
  public function getNextProduct() {
        if(is_null($this->ids))
        {
            $this->ids = $this->fetchProductIds();
        }
  
        if($this->getProductsLeft() == 0){
            return null;
        }
        $curId = array_shift($this->ids);
        $product_array =   $this->getProduct($curId);
        
        if(is_null($product_array)){
            $this->verboseOut('No product array for '.$curId);
            return new EmptyProduct();
        }
        
        
        try {
            $p = $this->createProduct($product_array);
        } catch (VublaException $e){
            Scraper::$errors[] = $e;
            $p = new EmptyProduct();
        }
              
        return $p;

    }
    
 
    
    
    /**
     * return SoapClient 
     */
    protected abstract function getClient();
    
    protected abstract function fetchProductIds();
    protected abstract function getProduct($id);
}
