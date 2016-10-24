<? 



/** 
*   The below class is not space efficient!
*   Enhance to only add newly updated products and take it all if forced to
*   
*/

class SmartWebClient implements ISoapCrawl {

    private $host;
    private $user = 'vubla';
    private $password;
    private $serviceUrl;
    private $soap;
    private $session_id;
    private $products;
    private $errorState = false;
    function __construct($host, $wid){
        $this->host = $host;
        $this->products = array();
        // Magento login information 
        $this->password = Settings::get('smart_web_client_pass', $wid);
        $this->user = Settings::get('smart_web_client_user', $wid);
        $this->serviceUrl = Settings::get('smart_web_client_wsdl', $wid);
        if(!isset($this->serviceUrl) || $this->serviceUrl == '')
        {
            $this->serviceUrl = 'https://api.smart-web.dk/service.wsdl';
        }
        
        if(!$this->user) {
            echo 'No User for smartweb - Error'.PHP_EOL;
            $this->errorState = true;
            return;
        }
        
        if(!$this->password) {
            echo 'No Password for smartweb - Error'.PHP_EOL;
            $this->errorState = true;
            return;
        }
        
        try {
            $this->soap = new SoapClient($this->serviceUrl );
            $this->soap->Solution_Connect(array('Username' => $this->user, 'Password' => $this->password));
            $this->soap->Solution_SetLanguage(array('LanguageISO' => 'DK')); // Indeed this should be received from settings
            $this->soap->Product_SetFields(array('Fields' => 'Id,ProductUrl,Price,DescriptionLong,Title,Pictures')); // Consider using GuidelinePrice
        } catch (SoapFault $e) {
            VublaMailer::sendOnargiEmail('Failed SmartWebshop login', 'It failed for host '. $this->host .' with wid '. $wid .'<br /><br /><pre>'.ob_get_clean().'<br /><br />'.print_r($e,true).'</pre>');
            var_dump($e);
            echo 'Error'.PHP_EOL;
            $this->errorState = true;
        }

    }

    function getErrorState(){
        return $this->errorState;
    }
    
    
    function getCategories(){ } 
    
     /**
     * This method retrieves a list of products.
     * 
     */
    function fetchProductIds(){
        try {
            $result = $this->soap->Product_GetAll()->Product_GetAllResult->item;
        } catch (SoapFault $e){
            return null;
        }
        echo 'Products directly from SmartWeb:'.PHP_EOL;
        var_dump($result);
        $ids = array();
        foreach($result as $p){
            $ids[] = $p->Id;
            $this->products[$p->Id] = $p;
        }
            
        return $ids;
    
    }
    
    
    
    
    function getXML($pid){
        $product = $this->products[$pid];
        $image = $product->Pictures->item;   //Consider save all pictures
        if(is_array($image)) {
            $image = $image[0]->FileName;
        } else {
            $image = $image->FileName;
        }
        $product->ProductUrl = substr($product->ProductUrl,1);
        
        $xml = array();
        
        @$xml['image_link'] =  $image;
        $product->Pictures = null;
        foreach($product as $key=>$val){
            if(!is_null($val)) {
                $key = $this->translateXml($key);
                $xml[$key] = $val; 
             
            }
        }
       
        
        //echo 'XML is: '.$xml.PHP_EOL;
        return $xml;
    
    }
    

    
    private function translateXml($key){
    
        switch ($key){
            case 'ProductUrl':
                $key = 'url';
                break;
            case 'Title':
                $key = 'product_name';
                break;
            case 'Price':
                $key = 'product_price';
                break;
             case 'DescriptionLong':
                $key = 'product_description';
                break;
            case 'Id':
                $key = 'pid';
                break;
        }
        
        return $key;
    }


}






?>