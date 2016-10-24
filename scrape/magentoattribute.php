<?php 



class MagentoAttribute extends MagentoAttributeBase {
      protected static $notWorking = array('model', 'page_layout', 'custom_design_from','custom_design_to','msrp_display_actual_price_type','activation_information',
                                           'msrp','msrp_enabled', 'news_from_date', 'news_to_date','price','enable_googlecheckout' , 'cost','minimal_price',
                                           'required_options', 'old_id','is_recurring','recurring_profile','status');
    
    
    static function get($client, $code,$data = array())
    {
        if(!isset(self::$_entities[$code]))
        {
            self::$_entities[$code] = new MagentoAttribute($client, $code,$data);
        }
         return self::$_entities[$code]; 
    }
    
    public static function saveAll($wid)
    {
        foreach (self::$_entities as $code => $attribute) {
            if(is_object($attribute)){
                 
               $q = "update options_settings set importancy = ? where name = ? and importancy = ?";
               $stm = vdo::webshop($wid)->prepare($q);
               $stm->execute(array( $attribute->isSearchable(), $code, 0));
               $stm->closecursor();
            }
        }    
    }
    
    
    private $_code;
    private $_displayName;
    protected $_options;
    protected $_data;
    protected $_isSearchable = false; 
  
    protected function __construct(MagentoSoapClient $client, $code,$data)
    {
        parent::__construct($client);
        $this->_code = $code;
        $this->_data = $data;
    }
    
    public function _load()
    {
            if(is_null($this->_options)){
                $this->_options = array();
                
                if($this->_code == 'vbl_dummy' or in_array($this->_code, self::$notWorking))
                {
                    return $this->_options = array();
                }
                $attribute_info_array = $this->_client->call('product_attribute.info', array($this->_code), 1);

                if(is_null($attribute_info_array))
                {
                    //Clear possible error and try alternative
                    $this->_client->getLastError();
                    $attribute_options_array = $this->_client->call('catalog_product_attribute.options', array($this->_code), 1);
                    $e = $this->_client->getLastError();
                    if(is_null($attribute_options_array) && $e != 0)
                    {
                        throw new UnsupportedFeatureException("This shop does not support this feature: 'product_attribute.info'. Error: " . $e);
                    }
                    if(is_array($attribute_options_array))
                    {
                        $attribute_info_array = array('options' => $attribute_options_array);
                    }
                    else 
                    {
                        self::$notWorking[] = $this->_code;  
                    }
                }
                if(isset($attribute_info_array['options']))
                {
                    foreach($attribute_info_array['options'] as $attribute_info)
                    {
                        if(!empty($attribute_info['value']))
                        {
                            if(is_object($attribute_info['value']) || is_array($attribute_info['value']))
                            {
                              //  throw new Exception("We got something weird, go this as a value: " . print_r($attribute_info['value'], true));
                            } else {
                                $this->_options[$attribute_info['value']] = $attribute_info['label'];
                            }
                        }
                    }
                }
                    
                if(isset($attribute_info_array['frontend_label']) && 
                    isset($attribute_info_array['frontend_label'][0]) && 
                    isset($attribute_info_array['frontend_label'][0]['label']) )
                {
                    $this->_displayName = $attribute_info_array['frontend_label'][0]['label'];
                }
                
                $this->_isSearchable = (isset($attribute_info_array['is_searchable']) && ($attribute_info_array['is_searchable']))? true : false;
            }
            return $this->_options;
    }
    
    public function getOptionLabel( $value)
    {
        $this->_load();
        if($this->isBoolean())
        {
            if($value)
            {
                if(!is_null($this->_displayName))
                {
                    return $this->_displayName;
                }
                return $this->_code;
            }
            else {
                return '';
            }
        }
        $values = $this->explodeAndValidateIds($value);
        if(is_array($values))
        {
            $result = array();
            foreach ($values as $val) 
            {
                if(isset($this->_options[$val])){
                     $result[] = $this->_options[$val];
                }
            }
            
            if(!empty($result))
            {
                return implode(', ', $result);
            }
        }
        return $value;
       
    }
    
    public function isSearchable()
    {
        $this->_load();
        if(!in_array( $this->_isSearchable, array(0,1,2,3,"0","1","2","3")))
        {
            return '0'; 
        }
        return (string) $this->_isSearchable;
    }
         
    private function explodeAndValidateIds($codes)
    {
        if(!is_array($this->_options))
        {
            return false;
        }
        $ids = explode(',', $codes);
        foreach ($ids as $id) 
        {
            if(!$this->isValidId($id))
            {
                return false;
            }
        }
        return $ids;
    }
    
    private function isValidId($id)
    {
        return is_int($id) || ctype_digit($id);
    }
    
    private function isBoolean()
    {
        return array_key_exists('type', $this->_data) && $this->_data['type'] == 'boolean';
    }
}
