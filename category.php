<?php
/**
 * Category, for each product there is a Category
 *
 * @package default
 * @author  Vubla
 */
class Category {
    
    private $data;
    public $cid;
    public $parent_id;
    public $name;
    public $wid;
    public $description = '';
    public $is_active = 1;
    public static $cats = array();
    function __construct($wid = null){
        $this->wid = $wid;
    }
    
  
    function save($wid = null){
    	if(!is_null($wid)){
    		$this->wid = $wid;
    	}
        if(is_null($this->wid)){
            throw new VublaException('Missing wid');
        }
        if(is_null($this->cid) || is_null($this->name) || is_null($this->parent_id)) return false;
        
        $db = VPDO::getVdo(DB_PREFIX.$this->wid);
        
        if(!$db->fetchOne('select count(*) from categories'.ScrapeMode::getPF().' where cid = ?',array($this->cid)))
        {
            $stm = $db->prepare('insert into categories'.ScrapeMode::getPF().' (cid, name, parent_id,description, is_active) values (?,?,?,?,?)');
     
            $stm->execute(array($this->cid, $this->name, $this->parent_id,$this->description, $this->is_active));
            $stm->closeCursor();
        }
        self::$cats[$this->cid] = &$this;
         
        return true;
    }
    
	/* This should not be used, category sets should be used instead */
    static function saveAll($categories, $wid){

        foreach ($categories as $category) {
            $cat = new category( $wid);
            $cat->cid = $category['id'];
            $cat->parent_id = $category['parent_id'];
            $cat->name = $category['name'];            

            $cat->save();
        }
    }
    

    
}


?>