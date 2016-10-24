<?php 

checkConfig();


abstract class Scraper extends AnyScrapeObject {
    
    /**
     *
     * @var  CatalogFetcher 
     */
    protected  $fetcher; 
    
    public $tables  = array('words', 
                              'word_relation',
                              'products',
                              'categories',
                              'options', 
                              'options_values',
                              'property_identifier');
   
    
    /**
     * Contains an array of all catched errors. 
     * @var type 
     */
    static $errors = array();
    
    /**
     *
     * @var SolrDocumentFactory
     */
    private $solrDocFactory; // skal merges ind i vublaSolrClient
    private $solrClient;    // skal merges ind i vublaSolrClient
    private $vublaSolrClient;
    public function __construct($wid,$verbose = null) 
    {
        parent::__construct($wid,$verbose);
        $this->fetcher = $this->getFetcher();
        if(settings::get('enable_solr_scrape', $this->getWid()))
        {
            try {
            $this->vublaSolrClient = new VublaSolrClient($this->getWid());
            $solr_hostname = "77.66.51.4";
            $solr_port = "8080";
            $this->solrDocFactory = new SolrDocumentFactory($this->getWid());
            $webshop_name = "webshop_".$this->getWid();
            
            $path = "solr/".$webshop_name;
            $this->solrClient = new SolrClient(array("hostname"=>$solr_hostname, "port"=>$solr_port, "path"=>$path));
            } catch (Exception $e)
            {
                echo "Wid: ". $this->getWid(); var_dump($e);
            }
        }
       
    }
    
  
    
    /**
     * Scrapes the current webshops catalog 
     */
    final public function scrape(){
        VOB::_n('Preparing...');
        $this->prepare();
        VOB::_n('Done Preparing');
        VOB::_n('Scraping Categories...');
        $i = 0 ;
       
        while($c = $this->fetcher->getNextCategory())
        {
            
             $c->save($this->wid);
            if($i % 10 ==  0)
            {
             
                VOB::_n('c '.$this->fetcher->getCategoriesCompletionsPercentage(). "%");
            }
            $i ++;
           
        }
         
         
        VOB::_n('Done scraping Categories...');
        VOB::_n('Scraping products...');
        $i = 0 ;
        while($p = $this->fetcher->getNextProduct()){
        
            
           
           if(settings::get('enable_solr_scrape', $this->getWid()) && !($p instanceof EmptyProduct))
           {
               
              if(!is_null($this->solrDocFactory)) { 
                $doc = $this->solrDocFactory->parseVublaProduct($p);
                try{
                $updateResponse = $this->solrClient->addDocument($doc);
                if(!is_object($updateResponse))

                {
                    print_r($p);
                } else {
                    print_r($updateResponse->getResponse());
                }
                }
                catch (SolrClientException $e)
                {
                  VOB::_n("Exception " .$e->getMessage());
                }
              }
           } 
           
           $p->save();
            if($i % 10 == 0){
                $percent = $this->fetcher->getProductsCompletionPercentage(). "%";
                VOB::_n($percent);
                VPDO::getVdo(DB_METADATA)->exec("update crawllist set status = '".$percent."' where wid = " . $this->getWid());
            } 
            $i ++;
            
        }
        OptionHandler::correctOptionsSettings($this->getWid()); 
        VOB::_n('Done scraping products...');
        
    }
    
    abstract protected function getFetcher();
    
    
  
  
    /**
     *  Set to True and it will echo results
     * @param boolean $b 
     */
    final public function outputEnabled( $b){
        if($b){
           
           VOB::setTarget(VOB::TARGET_STDOUT);
        } else {
            VOB::setTarget(VOB::TARGET_NONE);
        }
    }
    
    
    public function prepare(){
        if(ScrapeMode::get() == 'update'){
            VOB::_n('Running in update mode.');
            return true;
        }
    	$mdo = VPDO::getVdo(DB_METADATA);
    	$pdo =  VPDO::getVdo(DB_PREFIX.$this->wid);
    	foreach($this->tables as $table_name){
                $q[] = 'DROP TABLE  ' . $table_name . ScrapeMode::getPF();
    	
    	}
        @$pdo->execArray($q);
        $q = array();
        
        $w = new Webshopdbmanager();	
        $qu = 'CREATE DATABASE  '.DB_PREFIX . '__temp' . ';';
        $qu .= " \n \n USE ".DB_PREFIX.'__temp' . ";";
        $qu.= " \n \n " . $w->get_database_structure(); //var_dump($qu); exit;
        $qarr = explode(';', $qu);
        $mdo->execArray($qarr);
		
        foreach($this->tables as $table_name){
                $q[] = 'CREATE TABLE  ' . $table_name . ScrapeMode::getPF() .' LIKE ' . DB_PREFIX.'__temp.' . $table_name.'';

        }
        
        $q[] = 'DROP DATABASE '.DB_PREFIX.'__temp';
        $mdo->exec('use '. DB_METADATA );
        $pdo->exec('use '. DB_PREFIX.$this->wid );
	    @$pdo->execArray($q);
        
    }
	
    public function finish(){
        if(settings::get('enable_solr_scrape', $this->getWid()))
        {
            $this->vublaSolrClient->commit();
          //  $this->solrClient->commit();
        }
        $pdo =  VPDO::getVdo(DB_PREFIX.$this->wid);
        if($pdo->fetchOne('select count(*) from products'.ScrapeMode::getPF()) < 1){
            
            return false ;  
        } 
        
        $org_mode = ScrapeMode::get();
        if(ScrapeMode::get() == 'full'){
            
            $sql = "update  words".ScrapeMode::getPF()." wt   inner join words  w on wt.word = w.word set wt.rank = w.rank   ";
            $pdo->exec($sql);
            $q = array(); 
            $q1 = array();
            $q2 = array();
            foreach($this->tables as $table_name){
                    $q[] = 'DROP TABLE  ' . $table_name;
            }

            $pdo->execArray($q);
            foreach($this->tables as $table_name){
                $q1[] = 'CREATE TABLE  ' . $table_name . ' LIKE ' . $table_name . ScrapeMode::getPF();
                $q1[] = 'INSERT INTO ' . $table_name .' select * from  ' . $table_name . ScrapeMode::getPF();
            }
            $pdo->execArray($q1);
            foreach($this->tables as $table_name){
                    $q2[] = 'DROP TABLE  ' . $table_name . ScrapeMode::getPF();
            }
            $pdo->execArray($q2);

            $this->_properties = array();
            
            $this->updateBoost();
           
        }
       
        ScrapeMode::set('update'); /// As after this point the _tmp tables are removed, we can then put it in updater mode to ensure no _tmp is referecced
        MagentoAttribute::saveAll($this->wid);
        MagentoAttribute::clear();
        MagentoAttributeSet::clear();
        OptionHandler::correctOptionsSettings($this->getWid()); 
        $this->buildProductOptionsTbl();
        ScrapeMode::set($org_mode->__toString());
        
        
        if($pdo->fetchOne('select count(*) from products') < 1){ 
            return false ;  
        }
        if($pdo->fetchOne('select count(*) from options_settings') < 1){ 
            return false ;  
        }
        
        return true;
    }
	
    
    
    
      function buildProductOptionsTbl(){
         $pdo =  VPDO::getVdo(DB_PREFIX.$this->wid);
         $pdo->exec(" 
            drop view IF EXISTS  product_options; 
 
            CREATE TABLE IF NOT EXISTS `product_options` (
              `product_id` int(11) NOT NULL,
              `name` text COLLATE utf8_unicode_ci  NULL,
              `price` decimal(20,4)  NULL,
              `description` text COLLATE utf8_unicode_ci  NULL,
              `buy_link` text COLLATE utf8_unicode_ci  NULL,
              `image_link` text COLLATE utf8_unicode_ci  NULL,
              `link` text COLLATE utf8_unicode_ci  NULL,
              `pid` int(11) NOT NULL,
              `discount_price` decimal(20,4)  NULL,
              `lowest_price` decimal(20,4)  NULL,
              `quantity` int(11)  NULL,
              `sku` text COLLATE utf8_unicode_ci  NULL,
              PRIMARY KEY (`product_id`),
              KEY `pid` (`pid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
            start transaction;
            lock table product_options write, options_settings read,options_values read, options read;
            truncate table product_options;
            insert into product_options select 
    `options_values`.`product_id` AS `product_id`,
    max((case when (`options_settings`.`r_display_identifier` = 'name') then `options_values`.`name` else NULL end)) AS `name`,
    max((case when (`options_settings`.`r_display_identifier` = 'price') then `options_values`.`name` else NULL end)) AS `price`,
    max((case when (`options_settings`.`r_display_identifier` = 'description') then `options_values`.`name` else NULL end)) AS `description`,
    max((case when (`options_settings`.`r_display_identifier` = 'buy_link') then `options_values`.`name` else NULL end)) AS `buy_link`,
    max((case when (`options_settings`.`r_display_identifier` = 'image_link') then `options_values`.`name` else NULL end)) AS `image_link`,
    max((case when (`options_settings`.`r_display_identifier` = 'link') then `options_values`.`name` else NULL end)) AS `link`,
    max((case when (`options_settings`.`r_display_identifier` = 'pid') then `options_values`.`name` else NULL end)) AS `pid`,
    max((case when (`options_settings`.`r_display_identifier` = 'discount_price') then `options_values`.`name` else NULL end)) AS `discount_price` ,
    max((case when (`options_settings`.`r_display_identifier` = 'lowest_price') then `options_values`.`name` else NULL end)) AS `lowest_price`,
    max((case when (`options_settings`.`r_display_identifier` = 'quantity') then `options_values`.`name` else NULL end)) AS `quantity` ,
    max((case when (`options_settings`.`r_display_identifier` = 'sku') then `options_values`.`name` else NULL end)) AS `sku` 
from 
    ((`options_settings` join `options` 
        on((`options_settings`.`name` = `options`.`name`)))
    join `options_values` 
        on((`options`.`id` = `options_values`.`option_id`))
    )    
group by `options_values`.`product_id`;
  unlock tables;
commit;
        ");
    } 
    
    
    
    public function updateBoost()
    {
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
    
    
    
    
}
