<?



abstract Class SetHandler {
    
   
    
    protected static function createSet($wid,$data = null, $setName = ''){
   
        if(!in_array('ISet', class_implements($setName))){
            throw new Exception("Set does not implement ISET", 1);
        }
        
        $set =  new $setName($wid);
        if(is_null($data)){
            $set->fillFromDb();
        } else {
            $set->fillFromData($data);
        }
        return $set; 
    } 
}



?>