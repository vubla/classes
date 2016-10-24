<?

interface ISet{
    
    public function fillFromDb();
    public function fillFromData($data);
    public function validate();
    public function save();
    
}

?>