<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}

//require_once('search.php');
/**
 * Vubla's own extension of the PHP Data Object
 * @author rasmus
 *
 */
class VPDO extends PDO {
	/**
	 * All connections
	 * @var VPDO
	 */
	protected static $_cons = array();
	
	/**
	 * Name of the Database
	 * @var string
	 */
	public $db;
	
    
    public static function meta()
    {
        return self::getVdo(DB_METADATA);
    }
    
    public static function webshop($wid)
    {
        return self::getVdo(DB_PREFIX.$wid);
    }
    
    
	/**
	 * Should not be called from outside the class
	 * @param string $db
	 * @param string $user
	 * @param string $pass
	 * @param unknown_type $options
	 * @throws VublaException
	 */
	public function __construct($db, $user = null, $pass = null, $options = null, $host = null)
	{
		//$this->db = $db;
		if(is_null($host)){
			if(defined('HOST')){
				$host = HOST;				
			} else {
				$host = 'localhost';
			}	
		}
		
        if(is_null($db)) {
            $dbnamestring = '';
        } else {
            $dbnamestring = ';dbname=' . $db;
        }
		
		parent::__construct('mysql:host=' . $host . $dbnamestring.';', $user, $pass, $options);
		$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $this->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
	}
	
	/**
	 * Gets an instance of a VDO. If a similar connection is already established this will be returned. 
	 * User and pass is only required if the connection was not previously made.
	 * @param string $db
	 * @param string $user
	 * @param string $pass
	 * @param unknown_type $options
	 */
	static function getVdo($db){
		// Hvis user og pass er null s� skal den  se om der er en eksisterende vdo med det det navn,
		//$con = ArrayTools::get_property_value_in_array(self::$_cons, 'db', $db);
		$con = null;
		
		if(isset(self::$_cons[$db]))
		{
		   $con = self::$_cons[$db]; 
		}
		
		if(is_null($con)){
			$con = new self($db, DB_USER, DB_PASS, null);
			self::$_cons[$db] = $con;	
		}
       
		return $con;
	} 
	
	/**
	 * Gets a list of objects. 
	 * If objname is specified a object of that name is inialized and filled with data from the db.
	 * If it cannot find the class or nothing given it uses stdClass.
	 * @param string $sql
	 * @param string $objname Name of object.
	 */
    function getTableList($sql,$objname = 'stdClass', $cond = null){
        $result = $this->prepare($sql);
        $result->execute($cond);
        $err = $this->errorInfo();
        if ($err[0] != '00000') {
            VublaLog::_("\nPDO::errorInfo():\n".print_r($this->errorInfo(),true)."The failing Query is<br/>\n".$sql."\n  <br/>");
            throw new VublaException("Error in GetTableList");
        }
		$list = null;
		/*if(class_exists($objname)){
			while($t = $result->fetchObject($objname)){
				$list[] = $t;
			}
		}*/
        while($t = $result->fetchObject()){
            if(class_exists($objname))
            {
		        $obj = new $objname;
		        foreach ($t as $key => $value) 
		        {
		        	$method_name = 'set'.ucfirst($key);
		        	if(method_exists($obj, $method_name))
		        	{
		        		$obj->$method_name($t->$key);	
		        	}
		        	else 
		        	{
		        		$obj->$key = $t->$key;
		        	}
                    
                }
                $list[] = $obj;
            }
            else 
            {
                $list[] = $t;
            }
                
        }
        $result->closeCursor();
        return $list;
	}

	/**
	 * Fetches one value of one row. 
	 * @param string $sql
	 */
	function fetchOne($sql, $cond = null){
		$result = $this->prepare($sql);
		$result->execute($cond);
		$val = $result->fetch(PDO::FETCH_NUM);
       /*
        if (!$result) {
            Log::_("\nPDO::errorInfo():\n");
            Log::_($this->errorInfo());
            throw new VublaException("Error in fetch one");
        }
        * */
        $result->closeCursor();
		return $val[0];
	}
	
	function execArray($qarr){
		foreach($qarr as $query){
		 	$query . "\n";
			$this->exec($query);
		}
	}
	
	function getRow($sql, $cond = null){
	   $stm = $this->prepare($sql);
	   $stm->execute($cond);

       $val = $stm->fetchObject();
       $stm->closeCursor();
      
       return $val;
	}

	function getRowArray($sql, $cond = null){
	   $stm = $this->prepare($sql);
	   $stm->execute($cond);
       $val = $stm->fetch();
       $stm->closeCursor();
      
	   return $val;
	}

	function fetchRowArray($sql, $cond = null)
	{
		return self::getRowArray($sql, $cond);
	}
	
	/**
	 * Returns the number of rows in the given sql
	 * @param string $sql
	 */
	function getRowCount($sql){
		$result = $this->prepare($sql);
		$result->execute();
        $val = $result->rowCount();
        $result->closeCursor();
		return $val;
	}
	
	
    static function reset($db){
	   @self::$_cons[$db] = null;
	   $con = new self($db, DB_USER, DB_PASS, null);
       self::$_cons[$db] = $con;
	}
	
	#######################################################
	########### USED TO MIGRATE TO NEW LOGGING  ###########
	###########   REMEMBER TO REQUIRE SEARCH    ###########
	#######################################################
	function imigrateOldSearchedWords() {
		$test = new SearchWord();
		$test->word = "test";
		$test->__set('short','t');
		$sql = "SELECT id,search_for FROM search_log";
		$all = $this->getTableList($sql);
		$log = new SearchLog();
		foreach($all as $row) {
			$str = $row->search_for;
			$arr = explode("[",$str);
			$j = 0;
			$sWords = array();
			while(isset($arr[$j*5+2])) {
				$sWords[$j] = new SearchWord ();
				for($i = 2+$j*5 ; $i < (1+$j)*5 ; $i++) {
					$temp = explode("] => ",$arr[$i]);
					$var = explode(":",$temp[0]);
					$val = explode(")",$temp[1]);
					$var = trim($var[0]);
					$val = trim($val[0]);
					$sWords[$j]->__set($var,$val);
				}
				$j++;
			}
			$log->saveWords($this , $row->id , $sWords);
		}
	}
    
    public function condCommit($condition){
        if($condition){
            $this->commit();
        }  
    }
    
    public function condBeginTransaction($condition){
        if($condition){
            $this->beginTransaction();
        }  
    }
    
    public function fetchSingleArray($sql, $cond = null){
        $result = $this->prepare($sql);
        $result->execute($cond);
        $err = $this->errorInfo();
        if ($err[0] != '00000') {
            VublaLog::_("\nPDO::errorInfo():\n".print_r($this->errorInfo(),true)."The failing Query is<br/>\n".$sql."\n  <br/>");
            throw new VublaException("Error in GetTableList");
        }
        $list = null;
        while($t = $result->fetch(PDO::FETCH_NUM)){
            $list[] = $t[0];
        }
        
     
        return $list;
    }

    public static function closeAll()
    {
        foreach(self::$_cons as $key=>$val)
        {
            self::$_cons[$key] == null;
        }
    }
}
