<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}

class DummyProductIdHandler extends ProductIdHandler 
{
    function __construct($wid)
    {
       parent::__construct($wid);
       $this->vdo = VPDO::getVDO(DB_PREFIX.$this->getWid());          
    }
    
    
    function getResults(array $pids)
    {
        if(empty($pids))
        {
            return array();
        }
        $sql = 'SELECT * FROM products WHERE id IN ('.implode(',', $pids).')';
        $stm =  $this->vdo->prepare($sql);
        $stm->execute();
        $product_list = array();
        
        $res = array();
        while($product = $stm->fetchObject())
        {
            $temp = new stdClass();
            $temp->vubla_product_id = $product->id;
            $temp->pid = $product->pid;
            $temp->name = '';
            $res[$product->id] = $temp;
        }
        $stm->closeCursor();
        return $this->maintainOrder($pids,$res);
    }
    
    protected function maintainOrder($orderedArray,$unorderedArray)
    {
        $result = array();
        $tempArray = array();
        $orderedSize = sizeof($orderedArray);
        $unorderedSize = sizeof($unorderedArray);
        foreach ($unorderedArray as $value) 
        {
            $tempArray[$value->vubla_product_id] = $value;
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
            throw new VublaException('Entry in unordered array (size '.sizeof($unorderedArray).') found in ordered array(size '.sizeof($result).')');
        }
        return $result;
    }
}
