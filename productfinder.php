<?php

if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}

define('REGEX','Regular expression');
/**
 * This class finds all products on a page. 
 * @author rasmus
 *
 */
class ProductFinder_deprecated {
	
	 /**
	  * The properties related to the webshop
	  * @var array[Property]
	  */
	private $_properties = array();
	public $tables;
	

	public  $wid;
	//static private $instance;
	public static $WID;
	
	function __destruct(){
		//$this->finish();
	}
	
	function __construct($wid){
	    if($wid < 1){
	       echo "No wid in productfinder";
	       exit;
	    }
		$this->wid =  $wid;
		$this->tables = array('words', 
		                      'word_relation',
		                      'products',
		                      'categories',
		                      'options', 
		                      'options_values',
                              'property_identifier');
		
		ProductFinder::$WID = $wid;
		$this->prepare();	
	}
	
	private function prepare(){
	    $mdo = VPDO::getVdo(DB_METADATA);
		$pdo =  VPDO::getVdo(DB_PREFIX.$this->wid);
		foreach($this->tables as $table_name){
			$q[] = 'DROP TABLE  ' . $table_name . '_tmp';
		//	$q[] = 'Truncate Table ' .$table_name . '_tmp';
		}
        @$pdo->execArray($q);
        $q = array();
        
        $w = new Webshopdbmanager();	
        $qu = 'CREATE DATABASE IF NOT EXISTS '.DB_PREFIX . '__temp' . ';';
        $qu .= " \n \n USE ".DB_PREFIX.'__temp' . ";";
        $qu.= " \n \n " . $w->get_database_structure(); //var_dump($qu); exit;
        $qarr = explode(';', $qu);
        $mdo->execArray($qarr);
		
		foreach($this->tables as $table_name){
			$q[] = 'CREATE TABLE  ' . $table_name . '_tmp LIKE ' . DB_PREFIX.'__temp.' . $table_name.'';
		//	$q[] = 'Truncate Table ' .$table_name . '_tmp';
		}
        
        $q[] = 'DROP DATABASE '.DB_PREFIX.'__temp';
        $mdo->exec('use '. DB_METADATA );
        $pdo->exec('use '. DB_PREFIX.$this->wid );
		@$pdo->execArray($q);
        
    }
	
	public function finish(){
	  
		$pdo =  VPDO::getVdo(DB_PREFIX.$this->wid);
	    if($pdo->fetchOne('select count(*) from products_tmp') < 1){
           return false ;  
        } 
		foreach($this->tables as $table_name){
			$q[] = 'DROP TABLE  ' . $table_name;
		}
		$pdo->execArray($q);
		foreach($this->tables as $table_name){
			$q1[] = 'CREATE TABLE  ' . $table_name . ' LIKE ' . $table_name . '_tmp';
			$q1[] = 'INSERT INTO ' . $table_name .' select * from  ' . $table_name . '_tmp';
		}
		$pdo->execArray($q1);
		foreach($this->tables as $table_name){
			$q2[] = 'DROP TABLE  ' . $table_name . '_tmp';
		}
		$pdo->execArray($q2);
		
		$this->_properties = array();
        
        if($pdo->fetchOne('select count(*) from products') < 1){ 
            return false ;  
        }
        if($pdo->fetchOne('select count(*) from options_settings') < 1){ 
            return false ;  
        }
	    $this->updateBoost();
        return true;
	}
	
	
	private function updateBoost(){
	    $pdo =  VPDO::getVdo(DB_PREFIX.$this->wid);
	   	
       $sql = 'UPDATE products 
       			SET boosted = 1
					WHERE id IN 
						(SELECT p.product_id
						FROM products_boost p
						WHERE action NOT LIKE \'deleted\'
						AND date_end >= ' . strtotime(date("Y-m-d",time()) . " 00:00:00") . '
						AND NOT EXISTS 
							(SELECT 1 
							FROM products_boost 
							WHERE product_id = p.product_id 
							AND id > p.id)
						)
					';
		$pdo->exec($sql);
	}
	


	function getProduct($productArr, $url = null){
	
		
		$product = new Product($this->wid);
        
        if(!isset($productArr['pid'])){
            if(VUBLA_DEBUG){
                echo 'Missing Product id. PID getProduct@ProductFinder in function :' . __FUNCTION__ .' in File:  ' . __FILE__ . '  ';
                var_dump($productArr);
            }
            return false;
        }
        $product->pid = $productArr['pid'];
        
		foreach($productArr as $key=>$property){
			
          	if($property != null){
    			$name = strtolower($key);
                if($name != 'option' && $name != 'category' && $name != 'categories' && !is_array($property)){
    			   
    			    $productArr['option'][] = array('name'=>$key, 'value'=>array('name'=>$property));
                  
                } 
                     
                
    		}
        }
       
        if(isset($productArr['category'])){
            $product->setCategories($productArr['category']);
        }
        if(isset($productArr['categories'])){
            $product->setCategories($productArr['categories']);
        }
        if(isset($productArr['option'])){
            $product->setOptions($productArr['option']);
        }
	   return $product;
		
		
	}

}

?>