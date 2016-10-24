<?php

class CategorySet extends  VublaIterator implements ISet {
   
    private $wid;
    private $db; 
    private $tree;
    public $errors;
    
    function __construct($wid){
    	if($wid < 1){
    		throw new Exception("Missing wid");
    	}
       $this->wid = $wid; 
       $this->db = VPDO::getVdo(DB_PREFIX.(int)$wid);
    }
    
    
    public function save(){
      // Calls the save method on every member of the set
     
      $this->map('save', $this->wid);  
	  
	
    }
    
  	public function removeCategories($names){
  		$removedParents = array();
  		
  		foreach($this->var as $key=>$cat){
  			if(in_array($cat->name, $names) || in_array($cat->cid, $names) ){
  				if(isset($removedParents['_'.$cat->parent_id])){
  					$removedParents['_'.$cat->cid] = $removedParents['_'.$cat->parent_id];
  				} else {
  					$removedParents['_'.$cat->cid] = $cat->parent_id;
				}
				unset($this->var[$key]);
  			}
			
  		}
		foreach($this->var as $key=>$cat){
			if(isset($removedParents['_'.$cat->parent_id])){

				$cat->parent_id = $removedParents['_'.$cat->parent_id];
	
			}
		}
		
  	}
    
    function fillFromDb(){
        $sql = 'select * from categories order by parent_id asc';
        $this->set($this->db->getTableList($sql, 'Category'));
    }
    
    function fillFromDbSpecial($listofcats){
     
        if(is_null($listofcats) || empty($listofcats)){
            $where = null;
        } else {
            $where = ' where ';
            $wheres = array();
            foreach($listofcats as $id) {
                $wheres[] = ' cid = '.$this->db->quote($id->ids) .' ';
            }  
            $where .= implode(' or ', $wheres);
           
        }
        $sql = 'select * from categories '.$where.' order by parent_id asc';
        $arr = $this->db->getTableList($sql, 'Category');
        if(!is_array($arr)) { $arr = array(); }
        $this->set($arr);
    }
    
    function fillFromData($data){
        if(is_null($data)) {
            $data = array();
        }
        $this->set($data);
        
    }
    
	
	
    function validate(){
        
    }
    
    
    function  getTreeList(){
        
        $already_passed = array();
        $min_parent = 100000;
        foreach ($this->var as $key => $elem) {
            if($min_parent > $elem->parent_id) $min_parent = $elem->parent_id;
                
            $already_passed[] = $elem->cid;
            
            $elem->child = array_filter($this->var,function ($in) use ($elem){
                if($in->parent_id == $elem->cid)
                    return true;
               
            });
            
        }

        return  array_filter($this->var,function ($in) use ($min_parent){
                if($in->parent_id == $min_parent){
                    return true;
                } else {
                    return false;
                }
            });
    }
    
    
    
}




    
    
