<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}

abstract class ProductIdHandler extends AnySearchObject {
    
    protected $result;
    protected $vdo;
	protected $productIds;
    
    function __construct($wid){
        parent::__construct($wid);
        $this->vdo = VPDO::getVDO(DB_PREFIX.$this->getWid());       
    }

    abstract function getResults(array $pids);
    
    protected function isRDisplayIdentifier($option) {
       if(is_null($option)){
            return false;
        }
        $q = 'SELECT name FROM options_settings WHERE `r_display_identifier` = ?';
        $res = $this->vdo->fetchOne($q,array($option));
        return $option == $this->getLowestPriceString() || isset($res);
    //    return !is_null($option) && ($option == $this->getLowestPriceString() || isset($this->vdo->fetchOne( 'SELECT name FROM options_settings WHERE `r_display_identifier` = ?',array($option))));
        
    }
}
