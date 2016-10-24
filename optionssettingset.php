<?


class OptionsSettingSet extends  VublaIterator implements ISet {
    
    private $wid;
    private $db; 
    public $errors;
    
    /**
     * @param $wid
     */
    function __construct($wid){
       $this->wid = $wid; 
       $this->db = VPDO::getVdo(DB_PREFIX.(int)$wid);
    }
    
    
    public function save(){
      $this->map('save', $this->wid);  
      
    }
    
  
    
    function fillFromDb($xtra_sql = ''){
        $sql = 'select * from options_settings '.$xtra_sql;
        $data = $this->db->getTableList($sql, 'OptionsSetting');
        
        $this->set($data);
    }
    
    
    
     /**
     * @return T
     */
    public function getValue($key)
    {
        foreach($this->var as $value){
           if($value->name == $key) {
               return $value;
            } 
            
        }
         throw new VublaException("The key '". $key. "'did not exist"); 
        
    }
     
     
     
    function fillFromData($data){
        
        foreach ($data as  $value) {
            $setting = new OptionsSetting();
            $setting->name = $value[0];
            $setting->importancy = $value[1];
            $setting->sortable = $value[2];
            $setting->r_display_identifier = $value[3];
            $setting->facet_type = $value[4];
            $this->var[] = $setting;
        }
    }
    
    /**
     * Bruges til at validere alle options i et sæt.
     */
    function validate(){
        /* Skal laves om, skal laves med en liste af elementer og så bruge translate til errors */
        $pricefound = false;
        $namefound = false;
        $linkfound = false;
        $list = $this->getFromAll('r_display_identifier');
        foreach ($list as $elem) {
            if($elem == 'price'){
                if($pricefound) { $this->errors[] = 'Der er angivet to eller flere priser!'; }
                $pricefound = true;
            }
            if($elem == 'link'){
                if($linkfound) { $this->errors[] = 'Der er angivet to eller flere produkt link!'; }
                $linkfound = true;
            }
            if($elem == 'name'){
                if($namefound) { $this->errors[] = 'Der er angivet to priser!'; }
                $namefound = true;
            }
        }
        if(!$pricefound){ $this->errors[] = 'Der er ikke angivet en pris'; }
        if(!$namefound){ $this->errors[] = 'Der er ikke angivet et navn'; }
        if(!$linkfound){ $this->errors[] = 'Der er ikke angivet et link'; }
        if(empty($this->errors)){
            return true;
        }
        return false;
    }
    
    
}


?>