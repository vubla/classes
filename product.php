<?php
if(!defined('DB_METADATA')){
    echo "No config";
    exit;
}
//vimport('Library.Application');

/**
 * The product
 * Properties desclared will not be saved in the extra property array.
 *
 */
class Product {

	/**
	 * List of properties
	 *
	 * @var array
	 */
	private $_properties = array();

	/**
	 * Name of the product
	 * @var stdclass
	 */
	//var $name;

	/**
	 * Price
	 * @var stdclass
	 */
	//var $price;

	/**
	 * Url of the product
	 * @var string
	 */
//	var $url;

	
    
	/**
	 *
	 * Desc
	 * @var stdclass
	 */
	//var $description;

   // var $buy_link;
   // 
 //   var $image_link;
    
    var $categories = array();
    
    var $pid;
    /** 
     * Webshop id 
     * @var int
     */
     var $wid;

    
    /**
     * We  store the options here temporarely. 
     * 
     */
    var $options = array();



    function __construct($wid){
        if($wid < 1){
            VublaLog::_n('No wid in product');
            exit;
        }
        $this->wid = $wid;
    }

    /**
     * Uses the PHP magic method __Set. If the property is not set it is stored in the property array.
     * Enter description here ...
     * @param unknown_type $name
     * @param unknown_type $value
     */
    function __set($name, $value){
            //var_dump($value);

            $this->_properties[$name] = $value;
    }

    /**
     * Read manual
     * @param unknown_type $name
     */
    function __get($name){
        if(isset($this->_properties[$name])){
            return $this->_properties[$name];
        }
            return null;
    }
    
    function setOptions($xml){
        if(sizeof($xml) < 1){
            $this->options= null;
        } else {
            $this->options = $xml;
        }
    }

    function setCategories($arr){
        if(sizeof($arr) < 1){
            $this->categories= null;
        } elseif(is_array($arr) ) {
            $this->categories = $arr;
        } else {
            $this->categories = array($arr);
        }
    }
    
    /**
     * Saves the product
     */
    function save()
    {
        $vdo = VPDO::getVdo(DB_PREFIX . $this->wid);
        $vdo->beginTransaction();
        if(ScrapeMode::get() == 'update')
        {
            $product_id = $this->delete();
        }

        $time = time();
        $sql = "INSERT INTO products".ScrapeMode::getPF()." (updated, pid) VALUES ('{$time}',{$this->pid}) ";
        $vdo->exec($sql);
        $product_id = $this->getProductId($this->pid);

        if(is_null($product_id))
        {
            $vdo->rollBack();
            VublaLog::_('A product were scanned, but no id came up <br/> This is a really serious problem <br/><pre>' .
            print_r($this,true).'</pre>' . " \n webshop number ". $wid. '<br> strlen($name) = ' . strlen($name).' strlen($url)' . strlen($url));

            return;
        }

        $this->saveCategories($product_id);
        $this->saveOptions($product_id);
        $this->findAndSaveLowestPrice($product_id);
        $vdo->commit();
    }
    
    function getProductId()
    {
        $vdo = vpdo::getVdo(DB_PREFIX . $this->wid);
        $sql = "SELECT id FROM products".ScrapeMode::getPF()." WHERE pid = ?  LIMIT 1 ";
        return $vdo->fetchOne($sql, array($this->pid));
    }
    
    /**
     * Deletes a product and all its realtion.
     * @return Int|bool the product_id if it already existed, false if did not exist
     */
    function delete()
    {
       $product_id =(int) $this->getProductId();
       if(!$product_id){
           return false;
       }
    
       $vdo = vpdo::getVdo(DB_PREFIX . $this->wid);

       $vdo->exec('delete from word_relation where product_id = '. $product_id);
       $vdo->exec('delete from products where id = '. $product_id);
       $vdo->exec('delete from options_values where product_id = '. $product_id);
       return $product_id;
    }
      
    function findAndSaveLowestPrice($prod_id)
    {
        if(is_null($this->vdo)){
            $this->vdo = VPDO::getVdo(DB_PREFIX . $this->wid);
        }
        $price_query =  "select ov.name 
                            from 
                                options_settings os 
                            inner 
                                join options".ScrapeMode::getPF()." o 
                            on 
                                o.name = os.name  
                            inner join 
                                options_values".ScrapeMode::getPF()." ov 
                            on 
                                o.id = ov.option_id  
                            where 
                                product_id = ".(int)$prod_id ."
                            and 
                                r_display_identifier = ";
        
        $discount_price = $this->vdo->fetchOne($price_query . "'discount_price'");
        $price = $this->vdo->fetchOne($price_query . "'price'");
        if(is_null($discount_price))
        {
            $lowest = $price;
        } else if(is_null($price))
        {
            $lowest = $discount_price;
        } elseif($price < $discount_price){
            $lowest = $price;
        }
        else {
            $lowest = $discount_price;
        }
        $this->options = array('name'=>'lowest_price','value'=>array('name'=>$lowest));
        $this->saveOptions($prod_id);
     }

      /** 
       * Assumes that there are more catecories
       * Also saves the categories in the options_settings(Implicit)
       */
      function saveCategories($product_id)
      {
        //var_dump(Category::$cats); exit;
        if(is_null($this->categories) || sizeof($this->categories) < 1){
            return;
        }
      
        foreach ($this->categories  as $ids) {
           if(empty($ids)){
              continue;
           }
        	if(isset(Category::$cats[$ids]) && is_object(Category::$cats[$ids])){
	            $names[] = array('name'=>Category::$cats[$ids]->name);
	            $cids[] = array('name'=>Category::$cats[$ids]->cid);
	            $i = 0;
	            $parent_id = $ids;
	           
	            while(($parent_id = Category::$cats[$parent_id]->parent_id) != 0){
	             
	                $names[] = array('name'=>Category::$cats[$parent_id]->name);
	                $cids[] = array('name'=>Category::$cats[$parent_id]->cid);
	                $i++;
	                if($i > 7 ){ $i = 0; break; }
	            }
	         	
	        // 	
	            	$this->saveWords(Category::$cats[$ids]->name, $product_id, 2);
	           	    $this->saveWords(Category::$cats[$ids]->description, $product_id, 1);
		//		}
			}
        }  
		if(isset($names)){
        	$this->options['category'] = array('name'=>'category','value'=>$names);
        	$this->options['category_id'] = array('name'=>'category_id','value'=>$cids);
		}
      }

	
	
	 function saveWords($words,$product_id,$field){
        $words = str_replace('&nbsp;','   ',$words );
       //  $words = str_replace(';',' ',$words );
        switch($field){
            case 1: $field = 'indesc'; break;
            case 2: $field = 'incategory'; break;
            case 3: $field = 'inname'; break;
            default:
                return;
        }
        if(is_array($words))
        {
         //   VOB::_n(print_r($words,true));
            return; 
        }
        $preppedWords = self::splitWords($words);
        foreach ($preppedWords as $word) {
            $word = new Word($word);
            $word->save($product_id, $field);
        }
		//*/
	}
	
	static function splitWords($words)
    {
        $words = html_entity_decode($words);
        $preppedWords = array();
        $words = str_replace('>','> ',$words );
        $words = str_replace('.','. ',$words );
        $words = strip_tags($words);
    
        $token = ' +|:, ./\(){}[]!?=&'."\n\r".PHP_EOL;
        $word = strtok($words, $token);
        $subworddelimiters = array( "–", "­" );
        $words_to_further_split = array();
        do {
             
            foreach($subworddelimiters as $del){
                $word = mb_replace($del, '-', $word);
            }
            
            if(strpos($word,'-') !== false){
                $words_delimited_hyphen = explode('-', $word);
                $new = array();
                foreach($words_delimited_hyphen as $w)
                {
                    if(($w) !== "")
                    {
                        
                        $new[] = $w;
                    }     
                }
                $i = 0;
              
                foreach($new as $w)
                {
                    if($i > 0)
                    {
                        $preppedWords[] = $new[$i-1] . '-' . $new[$i];
                    }
                    $i++;
                    $words_to_further_split[] = $w;
                    
                }
                $preppedWords[] = mb_replace('-', '', $word);
             
           
            }
           
            $word = Word::trimHyphen($word);
            $preppedWords[] = ($word);
           
          
        } while($word = strtok($token));
        
        
        foreach($words_to_further_split as $w){
           $preppedWords = array_merge(self::splitWords($w), $preppedWords);
        }
        return array_unique($preppedWords);
    }
    
    function saveOptions($product_id){
      
       
        $db = VPDO::getVdo(DB_PREFIX . $this->wid);
        if(is_null($this->options) || empty($this->options) || sizeof($this->options) == 0){
            return false;
        }
        
        /**
         * If the name is set then we know there is only one option
         * We then wrap it up in an array. 
         */
        if(isset($this->options['name'])){
           $this->options = array($this->options);
        }
        
        
        foreach ($this->options as $option) {
            $option_id = $db->fetchOne("select id from options".ScrapeMode::getPF()." where name = ".$db->quote($option['name']));
            if(!$option_id){
                // If it doesent already exist add the option
                $sql = "insert into options".ScrapeMode::getPF()." (name) values (".$db->quote($option['name']).")";
                $db->exec($sql);
                // Get the id, again.
                $option_id = $db->fetchOne("select id from options".ScrapeMode::getPF()." where name = ".$db->quote($option['name']));
            }
            
            /**
             * IF the internal array is named name then theres only one value.
             */
            if(isset($option['value']['name'])){
                // And we there buffer it into an array
                $option['value'] = array($option['value']);
            }
            
            foreach (array_unique($option['value'],SORT_REGULAR) as $value) {
                if(empty($value['name'])){
                    continue; // If empty val, no need to save
                }
                if(is_array($value['name'])){
                    echo "We want a string We got: ".print_r($value, true) . " in ". __FUNCTION__." " . __FILE__. PHP_EOL ;
                }
                if(is_array($option['name'])){
                    echo "We want a string We got: ".print_r($option, true) . " in ". __FUNCTION__." " . __FILE__ . PHP_EOL;
                }
               
                $importancy = $this->_isSavableOption($option['name'], sizeof($option['value']));
                if($importancy > 0)
                {
                    $this->saveWords( $value['name'],  $product_id, $importancy);    
                }   
                @$price_prefix = $db->quote($value['price_prefix']);
                @$price_value = $db->quote($value['value_price']);
              
                $sql = "insert into options_values".ScrapeMode::getPF()." (name,product_id, option_id, price_prefix, price) values (    
                                                                                          ?,?,?,?,?
                 )";
                $stm = $db->prepare($sql);
              //  echo $value['name'];
                $stm->execute( array( $value['name'],$product_id,$option_id,$price_prefix,$price_value));
                $stm->closecursor();
            }
        }
    }
	
    function _isSavableOption($name, $number_of_values){
        $db = VPDO::getVdo(DB_PREFIX . $this->wid);
        if($db->fetchOne('select count(*) from options_settings where name = ?', array($name)) < 1){
            $optSet = OptionHandler::createOptionsSetting($name, $number_of_values);
            $optSet->save($this->wid);
            $importancy = $optSet->importancy;
        } else {
            $importancy = $db->fetchOne('select importancy from options_settings where name = ?', array($name));       
        }
        return $importancy;
    }

    // Warning - fetches only one and not multiple if there are more than one.
    static function getProductProperty($prob_name,$prod_id, $wid){
        $vdo = vpdo::getVdo(DB_PREFIX.$wid)   ;
        return $vdo->fetchOne("select ov.name from options o  
                    inner join options_values ov on o.id = ov.option_id  where o.name = ? and product_id = ?", array($prob_name, $prod_id));
       }
    
    function getCleanOptions()
    {
        $opt = array();
        foreach($this->options as $key=>$value)
        {
         
      
           if(!isset($value['value']['name']))
           {
               continue;
            }
       
           $opt[$value['name']] = ($value['value']['name']);
        }
       
        return $opt;
    }
}
