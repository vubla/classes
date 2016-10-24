<?php 

checkConfig();


abstract class CatalogFetcher extends AnyScrapeObject {
    
    abstract function getNextProduct();
    abstract function getNextCategory();
    
    private  $products_total = null;
    private  $products_left = null;
    private  $categories_total = null;
    private  $categories_left = null;
    
    /**
     * Creates a product from an Array
     * Also converts old xml schemes into the new one. 
     * @param array $productArr
     * @return boolean|\Product 
     */
    protected function createProduct($productArr){
	
		
	$product = new Product($this->wid);
        
        if(!isset($productArr['pid'])){
           throw new ScrapeException("Missing pid");
        }
        $product->pid = $productArr['pid'];
        
		foreach($productArr as $key=>$property){
			
          	if($property != null){
    			$name = strtolower($key);
                    if($name != 'option' && $name != 'category' && $name != 'categories' && !is_array($property)){

                                $productArr['option'][] = array('name'=>$key, 'value'=>array('name'=>$property));

                    } 
                     
                
    		}
        }
       
        if(isset($productArr['category'])){
     
            $product->setCategories($productArr['category']);
        }
        if(isset($productArr['categories'])){
            
            $product->setCategories($productArr['categories']);
        }
        if(isset($productArr['option'])){
            $product->setOptions($productArr['option']);
        }
	   return $product;
		
		
	}
    
       
        protected function setProductsLeft($count){
            if(is_null($this->products_total)){
                $this->products_total = (int)$count;
            }
            $this->products_left = (int)$count;
            
        }
        
       
        protected function setCategoriesLeft($count){
            if(is_null($this->categories_total)){
                $this->categories_total = (int)$count;
            }
            $this->categories_left =( int)$count;
            
        }
        
        public function getProductsLeft(){
            return $this->products_left;
        }
        
        public function getProductsTotal(){
            return $this->products_total;
        }
        
        public function getCategoriesLeft(){
            return $this->categories_left;
        }
        
        public function getCategoriesTotal(){
            return $this->categories_total;
        }
        
        public function getProductsCompletionPercentage(){
            return $this->getCompletionPercentage($this->getProductsTotal(), $this->getProductsLeft());
            
        }
        public function getCategoriesCompletionsPercentage(){
            return $this->getCompletionPercentage($this->getCategoriesTotal(), $this->getCategoriesLeft());
            
        }
        
          private function getCompletionPercentage($total, $left){
            $completed = $total - $left;
            if($total == 0){
                return 0;
            }
            $percentage = (($completed)/$total)*100;
            return round($percentage,1);
        }
        
        
       
}
