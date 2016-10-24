<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}

/**
 * A property describer.
 */
class Property  {

	/**
	 * The name of the property
	 * @var string
	 */
	var $name;
	
	/**
	 * The start identifier of the property
	 * @var string
	 */
	var $start;
	
	/**
	 * The end identifier of the property
	 * @var string
	 */
	var $end;
	


	
	function __construct(){
		//$this->data = $row;
		$this->reset();
		
	}
	
	/**
	 * Fetches the product proprty from a page.
	 * @param unknown_type $page
	 */
	function getValueFromPage($page){
		// To make it work with regex.
		$this->end = str_replace('\/', '/', $this->end);
		$this->end = str_replace('/', '\/', $this->end);
		
		 $pattern = '/' . $this->start . '(.*?)' . $this->end . '/s';
		
		preg_match_all($pattern, $page, $array, PREG_SET_ORDER);
		
	 	return @$array[0][1];
		
	}
	
	function reset(){
		$this->value = '';
	}
	

}




/*
class FindStartState extends BaseState implements IState {
	private $_i = 0;
	function __construct(&$root){
		parent::__construct(&$root);
	
	}


	function act($byte){
		if(substr($this->root->start, $this->_i, 1) == $byte){
			if($this->_i >= strlen($this->root->start) - 1){
				$this->_i = 0;	
				$this->root->setState('RecordValue');
			}
			$this->_i++;
				
		} else {
			$this->_i = 0;
		}
	}
}



class RecordValueState extends BaseState implements IState {

	function act($byte){
	
		if($byte === '<'){
			$this->root->setState('SkipTag');
			$this->root->act('<');
		} else {
			
			$this->root->value .= $byte;
		}
	}
}





class SkipTagState extends BaseState implements IState {
	var $_e = 0;
	var $_t = 0;
	var $_tags = 0;
	var $tagToFind;
	var $skipped = '';
	
	function __construct(&$root){
		parent::__construct($root);
		$data = explode(" ", $this->root->start);
		echo $this->tagToFind = $data[0];
		
	
		
		
	}

		
	function act($byte){
	
		
		if(strcasecmp(substr($this->root->end, $this->_e, 1) ,$byte) == 0){
			$this->_e++;
		} else {
			
			$this->_e = 0;
		}

		if($this->_e >= strlen($this->root->end)){
			$this->root->setState('Finish');
			
		} else {
			if($byte == '>'){
				
				$this->root->setState('RecordValue');
			}
		}


			

	}
	 /*
	function act($byte){
	
		if($byte == '<'){
			$this->_e = 0;
			$this->_t = 0;
		}
		
		if(strcasecmp(substr($this->tagToFind, $this->_t, 1) , $byte) == 0){
			$this->_t++;
		} else {
			$this->_t = 0;
		}

		if($this->_t >= strlen($this->tagToFind)){
			$this->_tags++;
			

			
			
		} 
		
		if(strcasecmp(substr($this->root->end, $this->_e, 1) ,$byte) == 0){
			$this->_e++;
		} else {
			
			$this->_e = 0;
		}

		if($this->_e >= strlen($this->root->end)){
			if($this->_tags > 0){
				$this->_tags--;
			
				$this->_e = 0;
				$this->_t = 0;
				//if($byte == '>'){	
				$this->root->setState('RecordValue');
			//	}
			} else {
				
				$this->_e = 0;
				$this->_t = 0;
				$this->_tags = 0;
				$this->root->setState('Finish');
			}
		} else {
			if($this->_e == 0 && $this->_t ==0){
				
				
				$this->root->setState('RecordValue');
			}
		}


			

	}
	//
}



require_once 'ProductFinder.php';
require_once 'Property.php';
require_once 'PropertyFinder.php';
//require_once 'StateParser/Parser.php';
mysql_connect('rasmusprentow.dk','vubla','anmara12');


$parser = new ProductFinder();
//$parser->Parse(str_split( 'asd <a href=dddd> john milkysgab </a> asd <a href=http://www.test.com> test</a>'));
//$parser->DeclareProperty('Name', '<td colspan="2" valign="top" class="redbold">
//										MSI Wind U135 Silver Intel Atom N450 1.66GHz Netbook with 10" LED 1024x600, 1GB ...									</td>');
//$parser->DeclareProperty('Price', '<span class="redbold">$499.00</span>');



if(isset($_GET["url"])){
//	$parser->setUrl($_GET["url"]);
}
else {
//	$parser->setUrl('http://www.pcmarket.com.au/20697_MSI_Wind_U135_Silver_Intel_Atom_N450_166GHz_Netbook.php');
}
echo "<pre>";
	echo "Fandt f�lgende produkter:
	";
$parser->setUrl('http://www.pcmarket.com.au/20697_MSI_Wind_U135_Silver_Intel_Atom_N450_166GHz_Netbook.php');
$product = &$parser->GetProduct();
var_dump($product);
flush();
//$parser->setUrl('http://www.pcmarket.com.au/22029_Gigabyte_ATi_Radeon_HD_6870_1GB_PCI-Express_Video_C.php');
//$product = &$parser->GetProduct();
//print_r($product);

echo "</pre>";
mysql_close();
*/
?>