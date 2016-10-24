<?php
@include_once '../basetest.php';
@include_once '../../data_layer/vpdo.php';

class BaseDbTest extends BaseTest {
    
   /* abstract function getSql();
    
    protected function createDb(){
        define ('HOST', 'localhost');
        define ('DB_USER', 'phpunit');
        define ('DB_PASS', 'Trekant01');
        define ('DB_METADATA', 'dd');
        define ('DB_PREFIX','phpunit_');
        $sql = $this->getSql();
        $dbname = md5($sql);
        $q = "create database $dbname;";
        $q .= $sql;
        
    }
    */
    protected $metaVdo = NULL;
    protected $shopVdo = NULL;
    private $origmetadbname;
    private $origshopdbname;
    private $vdo;
    private $mainVdo;
    protected $wid = 1;
    function __construct() {
        $this->origshopdbname = 'phpunit_' . $this->wid;
        $this->origmetadbname = 'phpunit_metadata';
    }
    
    function buildDatabases() {
       // $this->mainVdo = VPDO::getVdo('phpunit_main');
        if($this->lockDatabases()) {
            $this->buildMetaDatabase();
            $this->buildShopDatabase();
        } else {
            throw new Exception('Failed to lock databases, cannot complete builing of databases, try running resetdb.sh');
            //$this->markTestIncomplete('Failed to lock databases, cannot complete builing of databases');
        }
    }
    
    public function buildMetaDatabase() {
        $this->getVdo()->exec('CREATE DATABASE '.$this->metadbname());
        $this->getVdo()->exec('USE '.$this->metadbname());
        $this->buildSpecificDatabase($this->origmetadbname);
        $this->metaVdo = VPDO::getVdo($this->metadbname());
    }
    
    public function buildShopDatabase($wid = null) {

        $this->getVdo()->exec('CREATE DATABASE '.$this->shopdbname($wid));
        $this->getVdo()->exec('USE '.$this->shopdbname($wid));
        $this->buildSpecificDatabase($this->origshopdbname);
        $this->shopVdo = VPDO::getVdo($this->shopdbname($wid));
        
    }
    
    protected function buildSpecificDatabase($db) {
        $this->execFromFile('../test_db_'.$db.'.sql');
    }
    
    private function execFromFile($file) {
        $q = file_get_contents($file);
        if(empty($q)) throw new Exception("No content in file: ". $file, 1);
        
        $this->getVdo()->exec($q);
    }
    
    function dropDatabases() {
      //  $this->mainVdo = VPDO::getVdo('phpunit_main');
        $this->dropMetaDatabase();
        $this->dropShopDatabase();
        $this->unlockDatabases();
         vdo::closeAll();
    } 
    
    protected function dropMetaDatabase() {
        $this->dropSpecificDatabase($this->metadbname());
       
    }
    
    protected function dropShopDatabase($wid = null) {
        $this->dropSpecificDatabase($this->shopdbname($wid));
       
    }

    protected function dropSpecificDatabase($db) {
        $this->getVdo()->exec('DROP DATABASE '.$db);
    }
    
    protected function getMainVdo()
    {
        if(!is_object($this->mainVdo)){
            $this->mainVdo = VPDO::getVdo('phpunit_main');
        }
        return $this->mainVdo;
    }
    
    protected function getVdo()
    {
        if(!is_object($this->vdo)){
            $this->vdo = VPDO::getVdo(null);
        }
        return $this->vdo;
    }
     
    protected function lockDatabases() {
        return true;
        $res = $this->getMainVdo()->exec('UPDATE `test_mutexes` SET `locked` = 1 WHERE `name` = '.$this->getVdo()->quote('db_mutex_'.$this->shopdbname()).' AND `locked` = 0');
        return $res == 1; // There should be exactly one change
    }
    
    protected function unlockDatabases() {
        return true;
        $res = $this->getMainVdo()->exec('UPDATE `test_mutexes` SET `locked` = 0 WHERE `name` = '.$this->getVdo()->quote('db_mutex'.$this->wid));
        return $res == 1; // There should be exactly one change
    }
    
    function resetDatabases() {
        $name = $this->getVdo()->fetchOne('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '.$this->getVdo()->quote($this->metadbname()));
        if(isset($name)) {
            $this->dropMetaDatabase();
        }
        
        $name = $this->getVdo()->fetchOne('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '.$this->getVdo()->quote($this->shopdbname()));
        if(isset($name)) {
            $this->dropShopDatabase();
        }
        
        $name = $this->getVdo()->fetchOne('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '.$this->getVdo()->quote('phpunit_main'));
        if(!isset($name)) {
            $res = $this->getVdo()->exec('CREATE DATABASE phpunit_main');
        }
        
        $this->mainVdo = VPDO::getVdo('phpunit_main');
        $name = $this->getVdo()->fetchOne('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME = '.$this->getVdo()->quote('db_mutexes')).' AND TABLE_SCHEMA = '.$this->getVdo()->quote('phpunit_main');
        if(!isset($name)) {
            $res = $this->mainVdo->exec('CREATE TABLE IF NOT EXISTS `test_mutexes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `locked` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
        }
        
        $name = $this->getMainVdo()->fetchOne('SELECT name FROM test_mutexes WHERE name = '.$this->getVdo()->quote('db_mutex'.$this->wid));
        if(isset($name)) {
            $this->unlockDatabases();
        } else {
            $res = $this->mainVdo->exec('INSERT INTO `test_mutexes` ( `locked` , `name` ) VALUES ( 0 , '.$this->getVdo()->quote('db_mutex'.$this->wid) . ' )');
            if($res != 1) {
                throw VublaException('Failed to insert db_mutex in test_mutexes');
            }
        }
    }

    protected function metadbname()
    {
        return DB_METADATA;
    }
    protected function shopdbname($wid = null)
    {
        if(is_null($wid)) $wid = $this->wid;
        return DB_PREFIX . $wid;
    }
 
    function daq($toDump)
    {
        var_dump($toDump);
        exit;
    }
}


?>
