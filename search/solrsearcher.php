<?php
if(!defined('DB_METADATA'))
{
    echo "No config";
    exit;
}

class SolrSearcher extends  ProductIdHandler {
    
    private $solrClient;
    private $queryString;
    
    function __construct($wid, &$search)
    {
        parent::__construct($wid);
        $this->queryString = $search;
    }
    
    function getResults(array $product_ids)
    {
        $vublaSolrUrl = "http://solr1.vubla.com:8080/vubla-search-interface/rest";
        if(defined('VUBLA_SOLR_URL'))
        {
            $vublaSolrUrl = VUBLA_SOLR_URL;
        }
        $this->searchresult = json_decode(file_get_contents($vublaSolrUrl.'/search/search/?q='.urlencode($this->queryString).'&wid='.$this->wid));
        
        //var_dump($this->searchresult); exit;
        //Set parameters to make loging work
        $this->searchresult->option_filter_time = 'N/A';
        $this->searchresult->sorting_time  = 'N/A';
        $this->searchresult->widget_factory_time  = 'N/A';
        $this->searchresult->string_filter_timer  = 'N/A';
        $this->searchresult->product_factory_time  = 'N/A';
        $this->searchresult->original  = $this->queryString;
        $this->searchresult->searchwords  = array();
        $this->searchresult->pids = $this->searchresult->products;
        $this->searchresult->products = array();
        foreach ($this->searchresult->pids as $key => $pid) 
        {
            $this->searchresult->products[$key] = new stdClass();
            $this->searchresult->products[$key]->pid = $pid;
        }
        foreach ($this->searchresult->did_you_mean as $key => $value) 
        {
            $value->word = $value->query;
            $value->rank = 100;
            $value->ending = NULL;
            $this->searchresult->did_you_mean[$key] = array();
            $this->searchresult->did_you_mean[$key][] = $value;
        }
        
        $this->searchresult->options_filter_time = 'N/A';
        
        return ($this->searchresult);
    }
    
    public function errors() {
        $this->errors = array();

        return $this->errors;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    function useRankedSearch()
    {
        $threshold = settings::get('ranked_search_threshold', $this->wid);
        if($threshold > 100)
        {
            return false;
        }
        if($threshold < 0)
        {
            return true;
        }
        $per = $this->vdo->fetchOne('select (select count(1) from words where rank > 0)/(select count(1) from words)*100 ');    
        
        return ($per > $threshold);
        
    }
    
    function setOptions($array) {
    }
    
    public function setSortOrder($string) {
    }
    
    public function setSortBy($sortBy){
    }
    
    public function setMinOptions($array){
    }
        
    public function setEqOptions($array){
    }
        
    public function setMaxOptions($array){
    }

    public function setStringFilter(StringFilter $newFilter)
    {
    }

    public function setProductFactory(ProductIdHandler $newFactory)
    {
    }

    public function setWidgetFactory(WidgetFactory $newFactory)
    {
    }
    

    public function getLayout($type = 0){
        $this->vdo->exec('USE '.DB_PREFIX.$this->wid);
        $layout = $this->vdo->fetchOne("SELECT html FROM layouts WHERE type = ". $type . " ORDER BY id desc limit 1");
        if(is_null($layout)){
            $this->errors[] = "Missing layout"; 
        } 
        return $layout;
    }
    
    public function getEmptyLayout(){
        return $this->getLayout(1);
        
    }

    public function generateHtml($real_host, $file = '', $param = 'keywords', $content = null){
        
        if(!isset($this->result) || sizeof($this->result) == 0) {
            
            $content = $this->getEmptyLayout();
            $content = str_replace("[@besked]", "Din søgning på \"".$this->original."\" gav ikke noget resultat.", $content);
            $content = str_replace("[@keywords]", $this->original, $content);
            
        } else {
            $content = $this->getLayout();
            $content = $this->stringFilter->generateHtml($real_host,$file,$param,$content);
            $content = $this->userdefinedkeywords_search->generateHtml($real_host,$file,$param,$content);
        }
        return $content;
    }
    
}

