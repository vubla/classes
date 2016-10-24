<?php

if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}

#### 1851
//define('DB_PREFIX', 'vubla_');

class WebshopDbManager {
    
    private $id;
    
    /**
     * The url to the webshop without http:// and www.
     * @var string
     */
    private $url;

    /**
     * Name of the webshop
     * @var string
     */
    private $name;
    private $type;
    public $temp_structure;
    /**
     * The database object for the metadata database
     * @var VPDO
     */
    private $meta_vpdo;

    /**
     * The database object for a general connection. The is no specific database is selected.
     * @var VPDO
     */
    private $general_vpdo;

    /**
     * Tempory storage of identifiers
     * @var string
     */
    private $temp;
    
    /**
     * Constructer
     */
    public function __construct(){
        // Get the database object for the metadata database
        $this->meta_vpdo = VPDO::getVdo(DB_METADATA);

        // Set the general vpdo
        $this->general_vpdo = VPDO::getVdo(null);
    }

    /**
     * Returns the current webshop's id
     * magnus110426
     * Used to associate newly created DB with user in customers table
     */
    public function getWID(){
        return $this->id;
    }
    
    /**
     * Checks if the webshops exists already
     * 110812 - Joakim
     */
    public function check_webshop_exists($url) {
        try {
            $q = "SELECT `id` FROM `webshops` WHERE `hostname` = $url";

            $this->id = $this->meta_vpdo->fetchOne($q);
            return $this->id;
        } catch(VublaException $e) {
            throw $e;
        }
    }
    
    /**
     * Checks if the user exists already
     * 110812 - Joakim
     */
    public function check_user_exists($email) {
        try {
            $q = "SELECT * FROM `customers` WHERE `email` = $email";

            $this->id = $this->meta_vpdo->fetchOne($q);
            
            if(!is_null($this->id) || $this->id != 0){
                return true;
            }
        } catch(VublaException $e) {
            throw $e;
        }
        
        return false;
    }

    /**
     * Generates the webshop database.
     * And adds the structure.
     */
    public function generate($url,$subscription,$email,$name,$password,$company,$phone,$address,$address2,$postal,$city,$master){
        try {
            $this->insert_into_metadata($url,$subscription,$email,$name,$password,$company,$phone,$address,$address2,$postal,$city,$master);
          //  ZkAdminInterface::createConfig($this->id);
            $this->create_the_database($this->id);
            
            VublaMailer::sendPlainEmail('Ny bruger', 'Ny bruger:'. $email , 'Vubla Signup');
            Settings::setLocal('mage_api_key',$this->generateRandomToken(), $this->id);
            Language::get()->save($this->id);
            
        } catch (VublaException $e){
          VublaLog::_(print_r($e,true));  
          VublaLog::saveAll();
          throw  $e;
        }
    }

    public function generate_new($email,$password){
  
            $this->generate("''","'0'",$email,"''",$password,"''","''","''","''","''","''","''");
            
    }

    /**
     * In
     * @throws VublaException
     */
    private function insert_into_metadata($url,$subscription,$email,$name,$password,$company,$phone,$address,$address2,$postal,$city,$master,$soapType = null){
        // Reminder: TODO test if this new query which seckey works as expected 110508
        $this->meta_vpdo->beginTransaction();
        $token= $this->generateRandomToken();

        $q = "INSERT INTO `webshops` (`hostname`, `enabled`, `seckey`, `pack_id`, `next_pack_id`, `paydate`) VALUES (".$url.", '0', ".$this->meta_vpdo->quote($token).", " . $subscription . "," . $subscription . ", '" . strtotime("+30 days",time()) ."');";
             
        $affected_rows = $this->meta_vpdo->exec($q);
        if($affected_rows < 1){
            $this->meta_vpdo->rollBack();
            throw new VublaException('Nothing were inserted with query ' . $q, 11);
        }

        $q = "SELECT `id` FROM `webshops` WHERE `seckey` = ?";


        $this->id = $this->meta_vpdo->fetchOne($q, array($token));
        if(is_null($this->id) || $this->id == 0){
            $this->meta_vpdo->rollBack();
            throw new VublaException('Error in fetching id with query: '. $q , 12);
        }
    
        $q = "INSERT INTO `".DB_METADATA."`.`crawllist` (`wid`, `last_crawled`, `currentlybeingcrawled`) VALUES (" . $this->meta_vpdo->quote($this->id) . ", '0', '0');";
        
        $affected_rows = $this->meta_vpdo->exec($q);
        if($affected_rows < 1){
            $this->meta_vpdo->rollBack();
            throw new VublaException('Nothing were inserted with query ' . $q, 13);
        }
        
        if($soapType == null) {
            $q = "SELECT `id` FROM `soap_user_types` WHERE `name` = " . $this->meta_vpdo->quote('reg_user');
            $soapType = (int)$this->meta_vpdo->fetchOne($q);
            if(!isset($soapType)) {
                $soapType = 0;
                $msg = 'Missing reg_user soap type.<br/>
                        This is should not happen unless we have reset the db or something.<br/>
                        Please insert soap type reg_user';
                VublaMailer::sendOnargiEmail('Missing soap type',$msg);
            }
        }
        
        $q = "INSERT INTO ".DB_METADATA.".`customers` (`wid`, `email`, `pwd`, `name`, `company`, `phone`, `address`, `address2`, `postal`, `city`, `cookie`,`soap_type`, `master_id`, `on_config`) VALUES (" . $this->meta_vpdo->quote($this->getWID()) . ", " . $email . ", " . $password . ", " . $name . ", " . $company . ", " . $phone . ", " . $address . ", " . $address2 . ", " . $postal . ", " . $city . ", " .$this->meta_vpdo->quote($this->generateRandomToken()). ", ". $soapType . ', ' . $master . ",1);";
        
        $affected_rows = $this->meta_vpdo->exec($q);
        if($affected_rows < 1){
            $this->meta_vpdo->rollBack();
            throw new VublaException('Nothing were inserted with query ' . $q, 13);
        }
        
        
        $q = "SELECT `id` FROM `customers` WHERE `wid` = ". $this->id;


        $uid = $this->meta_vpdo->fetchOne($q);
        if(is_null($uid) || $uid == 0){
            $this->meta_vpdo->rollBack();
            throw new VublaException('Error in fetching id with query: '. $q , 12);
        }
        
        
        
        $q = "INSERT INTO ".DB_METADATA.".`configuration_progress` (uid, conf_steps_id, start) VALUES (".$uid. ",2, ".time().");";
        
        $affected_rows = $this->meta_vpdo->exec($q);
        if($affected_rows < 1){
            $this->meta_vpdo->rollBack();
            throw new VublaException('Nothing were inserted with query ' . $q, 13);
        }
   
         $this->meta_vpdo->commit();
        /*echo $q = "INSERT INTO ".DB_METADATA.".`payments` (`wid`, `price`, `paydate`, `paid`) 
            VALUES (" . $this->meta_vpdo->quote($this->getWID()) . ",
                        (select price * 100 as price from subscription_packages where id = ".$subscription."),
                         (select  paydate   from webshops where id = ".$this->meta_vpdo->quote($this->getWID())."), 0 )";
        $affected_rows = $this->meta_vpdo->exec($q);
        if($affected_rows < 1){
            throw new VublaException('Nothing were inserted with query ' . $q, 13);
        }
         */
    }


    // session thingy - also used for generating seckeys 110508
    private function generateRandomToken()
    {
        return md5(mt_rand(0,999999).mt_rand(0,999999).mt_rand(0,999999).'wop€()%#%_woptifucmmkingDooOo!!'.mt_rand(0,999999).mt_rand(0,999999).mt_rand(0,999999));
    }

    /**
     * Uses the general vpdo. 
     * @param string $db_identifier
     * @throws VublaException
     */
    private function create_the_database($db_identifier){
        if(is_null($db_identifier)){
            if(is_null($this->id)){
                throw new VublaException('Id is not determined', 13);
            } else {
                $db_identifier = $this->id;
            }
        }
        if(!ctype_alnum($db_identifier)){
        //  throw new VublaException('DB identifier not alphanumeric' . $db_identifier . ' end', 13);
        }
        $exist_stm = "SELECT EXISTS(SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".DB_PREFIX."$db_identifier')";
        $does_exist = $this->general_vpdo->fetchOne($exist_stm);
        try {       
            if($does_exist == 1){
            
                throw new VublaException('The database did already exist', 14);
          
            }
        } catch(VublaException $e)
        {
            VublaLog::_(print_r($e,true));  
            VublaLog::saveAll();
        }
        $q = 'CREATE DATABASE IF NOT EXISTS '.DB_PREFIX . $db_identifier . ';';
        $q .= " \n \n USE ".DB_PREFIX. $db_identifier . ";";
        $q .= " \n \n " . $this->get_database_structure(DB_PREFIX);
        $qarr = explode(';', $q);
        $this->general_vpdo->execArray($qarr);
        
        $q = 'INSERT INTO '.DB_PREFIX.$db_identifier.'.layouts (html, type) SELECT value,0 FROM '.DB_METADATA.'.standard_values WHERE name=\'searchlayout_1\'';
        $affected_rows = $this->general_vpdo->exec($q);
        
        
        if($affected_rows < 1){
            
            throw new VublaException('Nothing were inserted with query ' . $q, 13);
        }
        $q = 'INSERT INTO '.DB_PREFIX.$db_identifier.'.layouts (html, type) SELECT value,1 FROM '.DB_METADATA.'.standard_values WHERE name=\'emptysearchlayout_1\'';
        $affected_rows = $this->general_vpdo->exec($q);
        
        
        
        $this->general_vpdo->exec($q);
    
        if($affected_rows < 1){
            throw new VublaException('Nothing were inserted with query ' . $q, 13);
        }   
    

    }

    public function update_metadata(){
        throw new VublaException('Not implemented');
    
    }

    public function get_database_structure(){
        $q = "SELECT structure from webshop_structure order by id desc limit 1";    
        return base64_decode($this->meta_vpdo->fetchOne($q));
            
    }

    public function delete($db_identifier = null){
       if(is_null($db_identifier)){
            if(is_null($this->id)){
                throw new VublaException('Id is not determined', 13);
            } else {
                $db_identifier = $this->id;
            }
        }
        $q = "DELETE FROM webshops WHERE id = '$db_identifier'";
        $this->meta_vpdo->exec($q);
        $q = "DELETE FROM deleted_webshops WHERE id = '$db_identifier'";
        $this->meta_vpdo->exec($q);
        $q = "DELETE FROM crawllist WHERE wid = '$db_identifier'";
        $this->meta_vpdo->exec($q);
        $q = "DELETE FROM customers WHERE wid = '$db_identifier'";
        $this->meta_vpdo->exec($q);
        $q = "DELETE FROM deleted_customers WHERE wid = '$db_identifier'";
        $this->meta_vpdo->exec($q);
        $q =  "DELETE FROM payments WHERE wid = '$db_identifier'";
        $this->meta_vpdo->beginTransaction();
        $result = $this->meta_vpdo->exec($q);
        if($result > 1) {
            $this->meta_vpdo->rollback();
            $msg =  "While deleting user with WID " . $db_identifier . " i tried to remove him/her from payments, but I removed more than one entry. <br/><br/>" .
                    "I have rolled back, so the user might still be in the payments table -- i.e. the database is in an inconsistent state.";
            
            sendOnargieMail("Inconsistent DB State",$msg,null,3600);
        } else {
            $this->meta_vpdo->commit();
        }
        $q = "DROP DATABASE ".DB_PREFIX."$db_identifier" . ";";
        $this->meta_vpdo->exec($q);
    
    
    }
 
    public function setUrl($value){
        if(substr($value, 0, 7) == 'http://'){
            $value = substr($value, 7, strlen($value)); 
        } 
        
        if(substr($value, -1)== '/'){
            $value = substr($value, 0, strlen($value) -1);
        }

        $did_file_exist = self::url_exists ($value);
        
        //var_dump($did_file_exist);
        if(!$did_file_exist){
            throw new VublaException('The url could not be resolved to an valid homepage. ' . $value , 16);
        }
        $this->url = $value;
    }

    
    
    public static function url_exists($url) {
        // Version 4.x supported
        $handle   = curl_init($url);
        if (false === $handle)
        {
            return false;
        }
        curl_setopt($handle, CURLOPT_HEADER, false);
        curl_setopt($handle, CURLOPT_FAILONERROR, true);  // this works
        curl_setopt($handle, CURLOPT_HTTPHEADER, Array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15") ); // request as if Firefox
        curl_setopt($handle, CURLOPT_NOBODY, true);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);
        $connectable = curl_exec($handle);
        curl_close($handle);
        return $connectable;
    }
    
    

    public function update($updatequery, $simulate){
        $time = time();
        $db_identifier = 'temp_' . $time;
        
        $this->create_the_database($db_identifier);
        
        $dbname = DB_PREFIX . $db_identifier;
        $this->general_vpdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    
        $cmdArr = explode(";", $updatequery);
        $this->general_vpdo->exec("USE $dbname;");
        $errors = $this->runQuery($cmdArr);
        
        
        if(sizeof($errors) > 0){
          $this->delete($db_identifier);
          return $errors;
        }               
        $command = "mysqldump --no-create-db --skip-add-drop-table --lock-tables=false --no-data $dbname  -h " . HOST . " -u ".DB_USER." -p".DB_PASS." > dbschema/store";

        system($command);

        $temp_dump = file_get_contents('dbschema/store');
        
        if(!$simulate){
            
            $this->save_structure($temp_dump);
            $this->actual_update($updatequery);   
        } 
        
        $this->temp_structure = $temp_dump;
        $this->delete($db_identifier);
       
       
    }
    
    
    
    
    private function actual_update($query){
        $getWebshopList = "SELECT * FROM webshops";
        $objList = $this->meta_vpdo->getTableList($getWebshopList,'stdClass');
    
        $cmdArr = explode(";", $query);
        foreach($objList as $webshop){
            
            $this->general_vpdo->exec("USE ".DB_PREFIX."$webshop->id;");
            $this->runQuery($cmdArr);
        }
        if(DB_PREFIX == DB_PREFIX){
           VPDO::getVdo(DB_METADATA)->exec('INSERT INTO '. DB_METADATA .".query_log (query) values ('".base64_encode($query)."')");
        }
        
    //  $this->general_vpdo->exec($q);
    }

    public function setName($value){
        $this->name = $value;
    }
    
    
    
    
    private function runQuery($array){
       $errors = array();
       foreach($array as $q){
          //$q = $this->general_vpdo->quote($q);
        
        if($q != null && trim($q) != ''){
           if($this->general_vpdo->exec($q) === false){
              //$this->delete($db_identifier);
              $errors[] = $this->general_vpdo->errorInfo();
           }
        }

        
        }
        return $errors;
    }
    
    
    public function check(){
       $errors = array();
       $getWebshopList = "SELECT id FROM webshops";
        $objList = $this->meta_vpdo->getTableList($getWebshopList,'stdClass');
        foreach($objList as $webshop){
            $exist_stm = "SELECT EXISTS(SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".DB_PREFIX."$webshop->id')";
            $does_exist = $this->general_vpdo->fetchOne($exist_stm);
            if(!$does_exist){
                $errors[] = "Missing database for id = " . $webshop->id;
            } else {
                $good[] = $webshop->id;
            }
        }
        
         $dbList = $this->meta_vpdo->getTableList("show databases",'stdClass');
        foreach($dbList as $db){
            if($db->Database != "information_schema" && $db->Database != DB_METADATA){
                $dbpart = explode("_",$db->Database);
                if(@!in_array($dbpart[2], $good)){
                    $errors[] = "Database with no webshop name " . $db->Database;
                }
            }
        
        }
        return $errors;
    }
    
    
    
    private function save_structure($sql){
        
        $q = "INSERT INTO ".DB_METADATA.".webshop_structure  values ('', '". base64_encode($sql)."')";
        $this->meta_vpdo->exec($q);
        
    }
    
    
    public static function getWebshopTypes(){
        $db = VPDO::getVdo(DB_METADATA);
        return $db->getTableList('select * from webshop_types', '');
    }
    
    public static function getCurrentType( $wid){
        $db = VPDO::getVdo(DB_METADATA);
        $stm = $db->prepare('select wt.* from webshop_types wt inner join webshops w on wt.id = w.type where w.id = ?');
        $stm->execute(array($wid));
        $res = $stm->fetchObject();
        $stm->closeCursor();
        return $res;
    }
    
    public static function setWebshopType( $wid,$type){
        $db = VPDO::getVdo(DB_METADATA);
        if($d = $db->exec('update webshops set type = '.(int)$type.' where id = '.(int) $wid)){
            switch($type){
             case 1:
               // Settings::setLocal('encode_from', 'ISO-8859-1', (int)$wid);
                break;
             default:
             //   Settings::setLocal('encode_from', 'UTF-8', (int)$wid);
             
            }
        
           
            return true;
        } else {
            return false;
        }
      
         
         
    
	}
	
	/* should return all info of a package from the products 
	 * 
	 */
	public static function getPackageFromProducts($products)
	{
	       $lang_id = Language::get()->getId();
  
		$sql = 'select s.id, p.value from `subscription_packages` s inner join prices p using (price_id) where s.id < 4 and s.max_products >= ?  and language_id = ? order by s.max_products asc limit 1';
        $db = VPDO::getVdo(DB_METADATA);
        list($pack_id, $package_price) = $db->getRowArray($sql, array($products, $lang_id));  

		$package = new StdClass();
		$package->price = round($package_price,2);
		$package->id = $pack_id;
		return $package;
	
	}

  
}
