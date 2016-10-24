<?php



class SolrDocumentFactory extends SolrBase
{
        
   
    private $optionsSettings;
    
    
    function __construct($wid)
    {
         $this->optionsSettings = new OptionsSettingSet($wid);
         $this->optionsSettings->fillFromDb();
         
    }
    
    function parseVublaProduct(Product $product)
    {
        
        
        
        $options = $product->getCleanOptions();
        $doc = new SolrInputDocument();
        

        $speciel_case = array("products_name"=>"name","product_id"=>"pid", "sku"=>"sku", "pid"=>"pid");
       // var_dump($options);
        
        foreach($options as $key=>$value)
        { 
            if(isset( $speciel_case[$key]))
            {
                if(is_null($speciel_case[$key])) continue;
                $doc->addField($speciel_case[$key], $value);
            } 
            else
            {
                $importancy = 0;
                try {
                    $importancy= $this->optionsSettings->getValue($key)->importancy;
                } catch (VublaException $e)
                {
                }
                if($importancy == 0)
                {
                    continue;
                } 
                else 
                {
                    $doc->addField($key."_txt_".$importancy, $value);
                   // $doc->addField("txt_".$importancy, $value);
                }
            }
        }
        
        
        
        return $doc;
    }
    
    
    
    
}
