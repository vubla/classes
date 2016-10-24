<?



Class OptionsSetting {
    
    private $name; 
    private $importancy; 
    private $sortable;   
    private $r_display_identifier;    
    private $facet_type;
   
   
    static public $NOT_MULTIVALUE_OPTIONS = array('buy_link' , 
                                     'category',   
                                     'discount_price',
                                     'manufacturers_name' ,
                                     'products_description' ,
                                     'products_image' ,  
                                     'products_model' ,
                                     'products_name' ,
                                     'products_price' , 
                                     'url',
                                     'sku',
                                     'manufacturers_id');
    public function __get($name){
        // This is really unnecary and nicE_name is not used anymore(i Hope)
        switch($name){
            case 'nice_name':
                $val = str_replace('_', ' ', $this->name);    
                $val = ucfirst($val);  
                break;   
        }
        if(!isset($val)){
            if(!property_exists($this, $name)){
               throw new VublaException("Property '" .$name. "' does not exist in ".get_class($this) ,1);
            }
            $val = $this->$name;
        
        }
        return $val;
    }
    
    public function __set($name, $value){
        $this->$name = $value;
    }
    
    public function save($wid){
         $db = VPDO::getVdo(DB_PREFIX . $wid);
         if($db->fetchOne('select count(*) from options_settings where name = ?', array($this->name)) < 1){
            $db->exec('insert into options_settings (name, sortable) values ('.$db->quote($this->name).',\'\')')   ;
         }
         $stm = $db->prepare('update options_settings set name = ?, importancy =?, sortable = ?, r_display_identifier= ?, facet_type=? where name = ?');
         $stm->execute(array($this->name, $this->importancy, $this->sortable, $this->r_display_identifier   , $this->facet_type, $this->name));
         $stm->closeCursor();
    }
   
} 

?>