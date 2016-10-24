<?php

class ProductFactory extends ProductIdHandler{
    
    
    function __construct($wid)
    {
        parent::__construct($wid);
       $this->vdo = VPDO::getVDO(DB_PREFIX.$this->getWid());          
    }
    
    function generateFromPids($pids)
    {    
       return $this->getResults( $pids);
    }
    
    function getResults(array $product_ids)
    {

        if(sizeof($product_ids) < 1 ) return array();
        $vdo = $this->vdo;
        $wid = $this->getWid();
        $host = Settings::getLocal('host_name',$wid);
        //$api = 'http://api.vubla.com';
        $api = API_URL;
        
        $sql = 'select * from product_options where product_id = '. implode(' or product_id = ', $product_ids);
        
        $vat_multiplyer = (float)Settings::get('vat_multiplyer', $wid);
        //$length = (int)Settings::get('description_lenght', $wid); 
        $ownpicpath = Settings::get('picture_path', $wid);
        $default_picture_path = Settings::get('default_picture');
        $imagecache = Settings::get('use_image_cache',$wid);
        $stm =  $vdo->prepare($sql);
        $stm->execute();
        $product_list = array();
        
       
        
        $vat_func = $this->GetVatfunc($wid);
        
        
        
        while($product = $stm->fetchObject())
        {
    
            ############################################
            
            $product->price = number_format( (float) $vat_func(floatval($product->price) , $vat_multiplyer), 2,',' ,'.');
            $product->discount_price = number_format( (float)$vat_func( floatval($product->discount_price) , $vat_multiplyer), 2,',' ,'.');
            $product->lowest_price = number_format( (float) $vat_func(floatval($product->lowest_price) , $vat_multiplyer), 2,',' ,'.');
            //END MODIFY PRICE
            
            //START MODIFY LINKS
            if($product->image_link) 
            {   
                if($ownpicpath) 
                {
                    $product->image_link = $ownpicpath . $product->image_link;
                }
                if(strpos($product->image_link,'http://') === false && strpos($product->image_link,'https://') === false) 
                {
                    $product->image_link = 'http://'. $host . '/'.$product->image_link;
                } 
                if($imagecache == 1)
                {
                    $h = md5("super picture salt who you never guess. My name is rasmus and im super cool 1231564654231321".$product->image_link);
                    $debugurlstr='';
                    if(isset($this->debug) && $this->debug)
                    {
                        $debugurlstr='&debug=1';
                    }
                    $product->image_link = $api.'/cache/image.php?wid='.$wid.'&h='.$h.'&pid='.$product->pid.$debugurlstr.'&image_link='.$product->image_link;
                }
            }
            else 
            {
                $product->image_link = $default_picture_path;
            }
            
                
            if(strpos($product->link,'http://') === false) 
            {
                $product->link = 'http://'. $host . '/'.$product->link;
            }
            if(strpos($product->buy_link,'http://') === false) 
            {
                $product->buy_link = 'http://'. $host . '/'.$product->buy_link;
            }
            //END MODIFY LINKS
            
            //START MODIFY DESCRIPTION
            $length_desc = (int)Settings::get('description_lenght', $wid);
            $length_name = (int)Settings::get('product_title_lenght', $wid);
            $product->description = Text::substrword(strip_tags($product->description),$length_desc);
            $product->name = Text::substrword(strip_tags($product->name),$length_name);
           
            //$product->description = Text::substrword(strip_tags($product->description),$length);
            //END MODIFY DESCRIPTION
            $product_list[] = $product; 
        }
        $stm->closeCursor();
    //    var_dump($product_list); exit;
        return $this->maintainOrder($product_ids,$product_list);
       
    }
    
    


    
    protected function maintainOrder($orderedArray,$unorderedArray)
    {
        $result = array();
        $tempArray = array();
        $orderedSize = sizeof($orderedArray);
        $unorderedSize = sizeof($unorderedArray);
        foreach ($unorderedArray as $value) 
        {
            $tempArray[$value->product_id] = $value;
        }
       
        for($i = 0, $j = 0 ; $i < $orderedSize && $j < $unorderedSize ; $i++)
        {
            if(isset($tempArray[$orderedArray[$i]]))
            {
                $result[] = $tempArray[$orderedArray[$i]];
                $j++;
            }
        }
        
        if(sizeof($result) != sizeof($unorderedArray))
        {
            throw new VublaException('Entry in unordered array found in ordered array');
        }
        return $result;
    }
}


?>