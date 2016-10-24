<?php 


class MagentoAttributeSet extends MagentoAttributeBase
{
    
    
    static function get($client, $type, $set_id)
    {
       
        if(isset(self::$_entities[$type][$set_id]))
        {
            return self::$_entities[$type][$set_id];
        } 
        
        $setObject = new MagentoAttributeSet($client, $type, $set_id);
        if(isset(self::$_entities[$type]))
        {
            self::$_entities[$type][$set_id] = $setObject;
        }
        else 
        {
           self::$_entities[$type] = array($set_id=>$setObject);
        }
        return self::$_entities[$type][$set_id];
    }
    
   
    
   
    private $_type;
    private $_set_id;
    private $_attributes = null;
    private $_isSearchable = array();
    public  $timesLoaded = 0;
    
    protected function __construct($client, $type, $set_id)
    {
        parent::__construct($client);
        $this->_type = $type;
        $this->_set_id = $set_id;
    }
    
    private function _load()
    {
        if(is_null($this->_attributes)){
            $this->timesLoaded++;
            $this->_attributes = array();
            $res = $this->_client->call('catalog_product.listOfAdditionalAttributes', array($this->_type, $this->_set_id), 1);
            if(!is_array($res))
            {
                //Unfurtunately 'catalog_product.listOfAdditionalAttributes' is NOT supported in e.g. magento v1.4
                $res = $this->_client->call('product_attribute.list', $this->_set_id, 1);
                if(!is_array($res))
                {
                    throw new UnsupportedFeatureException("This shop does not support this feature: 'catalog_product.listOfAdditionalAttributes'/'product_attribute.list'");
                }
            }
            foreach($res as $value)
            {
                $this->_attributes[$value['code']] = MagentoAttribute::get($this->_client, $value['code'],$value);
            }
            $this->_attributes['vbl_dummy'] = MagentoAttribute::get($this->_client, 'vbl_dummy',array());
        }
        return $this->_attributes;
    }
    
  
    function getAttribute($code)
    {
        $data = $this->_load();
        
        if(!isset($data[$code]))
        {
            return $data['vbl_dummy'];
        }
        return $data[$code];
    }
   
}

