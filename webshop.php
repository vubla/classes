<?php 
if(!defined('DB_METADATA')){
	echo "No config";
	exit;
}

class Webshop {
	
	private $wid;
	function __construct($wid = 0) // As this can be used from db, we cannot have obligatory arguments
	{
		$this->wid = $wid;
	}

	function getWid()
	{
		return $this->wid;
	}

	function setWid($wid)
	{
		$this->wid = $wid;
	}

  /**
     * Returns  the parent
     * @param  integer $level (not implemented)
     * @return Webshop
     */
    public  function getParent($level = 1)
    {
    	$wid = VDO::meta()->fetchOne("select parent from webshop_relations where wid = ?", array($this->wid));
        if(is_null($wid))
        {
        	return null;
        }
        return new Webshop($wid);
    }

    /**
     * Returns siblings (including it self)
     * @param integer $level (not implemented)
     * @return Webshop[]
     */
    public  function getSiblings($level = 1)
    {
    	$parent = $this->getParent();
    	if(is_null($parent))
    	{

    		return array($this);
    	}
        return array_unique($parent->getChildren());
        

    }

    /**
     * Returns parent, siblings, and children, if any, otherwise empty an empty array
     * @param  integer $level (not implemented)
     * @return Webshop[]
     */
    public  function getFamily($level = 1)
    {
        $parent = $this->getParent();
        $result = array();
        if(!is_null($parent))
       	{
   			$result[] = $parent; 
       	} 
       	$result = array_merge($result,$this->getSiblings());
       	$result = array_merge($result,$this->getChildren());
       	
        
        return array_unique(array_values($result));
    }

    /**
     * Returns the children, if any, otherwise empty an empty array
     * @param  integer $level (not implemented)
     * @return Webshop[]
     */
    public  function getChildren($level = 1)
    {
        $result = VDO::meta()->getTableList("select wid from webshop_relations where parent = ?",'Webshop', array($this->wid));
        if(is_null($result))
        {
            return array();
        }
        return array_unique(array_values($result));
    }


    function __toString(){
    	return ''.$this->wid;
    }
}