<?php
if(!defined('DB_METADATA')){
	echo "No config";
	exit;
}

/**
 * class to alter and get settings, both global and local. 
 * There needs to be created two tables. One in vubla_metadata and one in vubla_webshop_* using notforanders. 
 * @author Alex and Rasmus
 */
class Settings {

	/** 
	 * First try to fetch a local if fails get a global, returns null if no setting with the given name exists
	 * Tested
	 */
	public static function get($name, $wid = 0){
		$result = null;
		if($wid) {
			$result = Settings::getLocal($name,$wid);
		}
		if(is_null($result)) {
			$result = Settings::getGlobal($name);
		}
		return $result;
	}
	
	 /** 
	 * Try to fetch local, on fail return null. 
	 * Tested
	 */
	public static function getLocal($name, $wid, $parent_call = false){
		if(!$wid){
			return null;
		}
		try	{
            if($name == 'host_name')
            {
                $vpdo = VPDO::getVDO(DB_METADATA);
                $result = $vpdo->fetchOne("SELECT hostname FROM webshops WHERE id = ?",array($wid));
            }
            else 
            {
            	/**
            	 * If it is a call to parent, we don't wan't to find stuff that has skip_inheritance. 
            	 * Note: If the parent has skip_inheriteance but a super parent doesn't we find the super parents value.
            	 * Skip inheritance simply means, do not inherit this value. 
            	 * @var String
            	 */
            	$skip_inheritance_check = $parent_call ? ' and skip_inheritance = 0 ' : '';

    			$vpdo = VPDO::getVDO(DB_PREFIX.$wid);
    			@$result = $vpdo->fetchOne("SELECT value FROM settings WHERE name = ? " . $skip_inheritance_check,array($name));
            }
		}
		catch(PDOException $e) {
			$result = null;
		}

		if(is_null($result))
		{
			$parent= (new Webshop($wid))->getParent($wid);
			if(is_object($parent) && $parent->getWid() > 0){
				$result = self::getLocal($name, $parent, true);
		    }
		}
		return $result; //TODO: Test what result is if fetch fails
	}
	
	 /** 
	 * Try to fetch a global setting value, on fail return null. 
	 * Tested
	 */
	public static function getGlobal($name){
		$vpdo = VPDO::getVDO(DB_METADATA);
		$result = $vpdo->fetchOne("SELECT value FROM settings WHERE name = ?",array($name));
		return $result;

	}

	/**
	 * Sets the global setting name to value
	 * Cannot add a new setting, only update
	 * @param string $name Name of the setting to change, max length 50
	 * @param string $value The value to set the name setting to
	 * Tested
	 */
	public static function setGlobal($name, $value){
		$vpdo = VPDO::getVDO(DB_METADATA);
		$sql = "UPDATE settings SET value = ".$vpdo->quote($value)." WHERE name = ".$vpdo->quote($name);
		$num = $vpdo->exec($sql);
		if($num != 1) {
			return false;
		}
		return true;

	}
	
	/**
	* Sets the value of name to value for webshop with id 'wid'
	* Checks that the name is an actual setting before setting it
	* Returns true on succes
	* Tested
	*/
	public static function setLocal($name, $value, $wid){
		if(!$wid){
			return false;
		}
		$local = VPDO::getVDO(DB_PREFIX.$wid);
		$local->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		$sql = "UPDATE settings SET value = ? WHERE name = ?";
        $stm = $local->prepare($sql);
        $stm->execute(array($value,$name));
        $num = $stm->rowCount();
        $stm->closeCursor();
		//$num = $local->exec($sql);
		if($num != 1) {
			$global = VPDO::getVDO(DB_METADATA);
			$sql2 = "SELECT * FROM settings WHERE name = ".$global->quote($name);
			$temp = $global->fetchOne($sql2);
			if(!$temp) {
				return false;
			}
			$sql3 = "SELECT * FROM settings WHERE name = ".$local->quote($name) . " AND  value = ". $local->quote($value);
			$temp = $local->fetchOne($sql3);
			if($temp) {
				return true;
			}
			$sql4 = "INSERT INTO settings(name,value) VALUES (?,?);";
			//$num = $local->exec($sql4);
            $stm = $local->prepare($sql4);
            $num = $stm->execute(array($name,$value));
            $num = $stm->rowCount();
            $stm->closeCursor();
			if($num != 1) {
				return false;
			}
		}
		return true;

	}

	/**
	* Gets the possible values of setting name
	* Returns null on failure
	* Tested
	*/
	public static function getPossibleValues($name)
	{
		$vpdo = VPDO::getVDO(DB_METADATA);
		$temp = $vpdo->fetchOne("SELECT possible_values FROM settings WHERE name = ?",array($name));
		if(!$temp)
		{
			return null;
		}
		$result = json_decode($temp,true);
		return $result;
	}

	/**
	* Sets the possible values for name to 'values'
	* Returns true if the opperation was succesful
	* Tested
	*/
	public static function setPossibleValues($name,$values)
	{
		$vpdo = VPDO::getVDO(DB_METADATA);
		$sql = "UPDATE settings SET possible_values = ".$vpdo->quote(json_encode($values))." WHERE name = ".$vpdo->quote($name);
		$num = $vpdo->exec($sql);
		if($num != 1) {
			return false;
		}
		return true;
	}

	/**
	* Gets the type of setting name
	* Returns null on failure
	* Tested
	*/
	public static function getType($name) {
		$vpdo = VPDO::getVDO(DB_METADATA);
		$result = $vpdo->fetchOne("SELECT type FROM settings WHERE name = ?",array($name));
		if(!$result)
		{
			return null;
		}
		return $result;

	}

	/**
	* Sets the type of name to type
	* Returns true if the opperation was succesful
	* Tested
	*/
	public static function setType($name,$type)
	{
		$vpdo = VPDO::getVDO(DB_METADATA);
		$sql = "UPDATE settings SET type = ".$vpdo->quote($type)." WHERE name = ".$vpdo->quote($name);
		$num = $vpdo->exec($sql);
		if($num != 1) {
			return false;
		}
		return true;
	}

	/**
	* Returns true if the setting is public, false if not
	* Returns null upon failure
	* Tested
	*/
	public static function isPublic($name) {
		$vpdo = VPDO::getVDO(DB_METADATA);
		$result = $vpdo->fetchOne("SELECT public FROM settings WHERE name = ?",array($name));
		if(!isset($result))
		{
			return null;
		}
		return $result != 0;

	}
	
	public static function getSettingsArray($wid = 0, $public = 1, $ignore_types = false)
	{
	    $meta = VPDO::getVDO(DB_METADATA);
	    $publicQueryString = '1 = 1';
	   if(is_array($public))
       {
           $publicQuoted = array_map(function ($elem) use($meta) {return $meta->quote($elem);}, $public);
           $publicQueryString = 'public IN ('.implode(',', $publicQuoted).')';
       }
       else 
       {
           $publicQueryString = 'public = '.$meta->quote($public);
       }
	   $q = 'SELECT * FROM settings s left outer join settings_descriptions sd on s.id = sd.settings_id WHERE (lang_id = ? or lang_id is null) and '.$publicQueryString;
	   
	   $list = $meta->getTableList($q, null,array(Language::get()->getId()));
	   $wtype = WebshopDbManager::getCurrentType($wid);
	   $real_list = array();
	   foreach($list as $setting){
	   		if(!$ignore_types){
		   		if($wtype->name != "magento"){ /// Nobody cares for this, unless they use magento
		   			if(strpos($setting->name, 'mage') !== false) continue;
		   		}
				if($wtype->name != "smartweb"){ /// Smartweb? We barely support it :P
		   			if(strpos($setting->name, 'smart_web') !== false) continue;
		   		}
				if($wtype->name == "magento"){ /// Magento are UTF-8! So no need for changes
					if(strpos($setting->name, 'encode_from') !== false) continue;
				}
			}
			$real_list[] = $setting;
			$setting->possible_values = Settings::getPossibleValues($setting->name);
            $loc = Settings::getLocal($setting->name,$wid);
        
	        if(!is_null($loc)){
	        
	           $setting->value = $loc;
	        }
		   
			
	   }
     

	   return $real_list;
	}
	
	/* NB: No verification */
	public static function setAllLocal($data, $wid){
	   foreach($data as $name=>$value){
			
			if($name == 'controller' || $name == 'submit') {
				continue;
			}
			if(!self::setLocal($name, $value,$wid)) {
				//return false;
			}
	   }
	   
	   return true;
	}
	
	/**
	* Gets the long name of setting
	* returns null on failure. Eg. no such setting
	* Tested
	*/
	public static function getLongName($name) {
		$vpdo = VPDO::getVDO(DB_METADATA);
		$result = $vpdo->fetchOne("SELECT sd.long_name FROM settings s inner join settings_descriptions sd on s.id = sd.settings_id WHERE name = ? and lang_id = ?",array($name, Language::get()->getId()));
		return $result;
	}
	
	/**
	* Gets the description of setting
	* returns null on failure. Eg. no such setting
	* Tested
	*/
	public static function getDescription($name) {
		$vpdo = VPDO::getVDO(DB_METADATA);
		$result = $vpdo->fetchOne("SELECT sd.description FROM settings s inner join settings_descriptions sd on s.id = sd.settings_id WHERE name = ? and lang_id = ?",array($name, Language::get()->getId()));
		return $result;	
	}
    
    /**
     * Fetches all settings and their translations
     */
    public static function getAllWithAllDescriptions()
    {
        $vpdo = VPDO::getVDO(DB_METADATA);
        return $vpdo->getTableList("SELECT id, name, (select sd.description from  `settings_descriptions` sd where sd.settings_id = s.id and lang_id = 1) as description_1, 
                                                     (select sd.description from  `settings_descriptions` sd where sd.settings_id = s.id and lang_id = 2) as description_2, 
                                                     (select sd.long_name from  `settings_descriptions` sd where sd.settings_id = s.id and lang_id = 1) as longname_1, 
                                                     (select sd.long_name from  `settings_descriptions` sd where sd.settings_id = s.id and lang_id = 2) as longname_2 
                             FROM settings s where s.public = 1");
                             
    }
    
    public static function setDescAndLongForAll($data)
    {
        $vpdo = VPDO::getVDO(DB_METADATA);
        $vpdo->beginTransaction();
        foreach($data['settings'] as $id=>$setting)
        {
            $lang = array(); 
            foreach($setting as $key=>$val)   
            {
                if(strpos($key, '_') !== false)
                {
                    list($type, $lang_id) = explode('_', $key);
                    $stm = $vpdo->prepare('delete from settings_descriptions where lang_id = ? and settings_id = ?');
                    $stm->execute(array($lang_id,$id));
                    $stm->closeCursor();
                    if(!isset($lang[$lang_id]) || !is_array($lang[$lang_id])){
                        $lang[$lang_id] = array();
                    }
                    $lang[$lang_id][$type] = $val;
                }
               
           
           }
           
           foreach($lang as $key=>$val)
           {
                $stm = $vpdo->prepare('insert into settings_descriptions (lang_id,settings_id, description, long_name) values(?,?,?,?)');
                if(!isset($val['description'])){
                    $val['description'] = null;
                }
                if(!isset($val['longname'])){
                    $val['longname'] = null;
                }
                $stm->execute(array($key,$id, $val['description'],$val['longname']));
                $stm->closeCursor();
           }
         
          
        }
        $vpdo->commit();
    }
}



