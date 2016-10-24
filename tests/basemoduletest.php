<?
@include_once '../basedbtest.php';

class BaseModuleTest extends BaseDbTest {
    
 
    function __construct() {
    	parent::__construct();
		$this->metadbname = DB_METADATA;
		
    }
    
	function deployModules($name,$version, $magento_version = ''){
	      echo "Executing: " . "../deploy_test_modules.sh " . $name . ' '. $version. ' ' . $magento_version . PHP_EOL;
		 if($this->lockDatabases()) {
		   exec("../deploy_test_modules.sh " . $name . ' '. $version . ' ' . $magento_version);
            $this->unlockDatabases();
        } else {
            throw new Exception('Failed to lock databases, cannot complete builing of databases, try running resetdb.sh');
          
        }
	}
	
	function removeModules($name,$version, $magento_version = ''){
         if($this->lockDatabases()) {
            echo "Executing: " . "../remove_test_modules.sh " . $name . ' '. $version. ' ' . $magento_version . PHP_EOL;
           exec("../remove_test_modules.sh " . $name . ' '. $version. ' ' . $magento_version);
            $this->unlockDatabases();
        } else {
            throw new Exception('Failed to lock databases, cannot complete builing of databases, try running resetdb.sh');
          
        }
    }
    
    

}


?>