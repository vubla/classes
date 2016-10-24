<?php
if(!defined('DB_METADATA'))
{
    echo "No config";
    exit;
}

class Searcher extends  ProductIdHandler {
    
	//private $product_result;
    //private $userdefinedkeywords_result;
    
    public $stringFilter;
    private $userdefinedkeywords_search;
    private $userdefinedkeywords_result;
    private $optionFilter;
    private $productFactory;
    private $widgetFactory;
    private $sortingFilter;


    
	
	function __construct($wid, &$search)
	{
        parent::__construct($wid);
        $this->searchresult = new SearchResult($wid);
        $this->userdefinedkeywords_search = new UserDefinedKeywordsSearcher($wid,$search);
        if($this->useRankedSearch())
        {
            $this->stringFilter = new ConjunctiveStringFilter($wid,$search, true);
        }
        else {
            $this->stringFilter = new StringFilter($wid,$search, true);
        }
        $this->optionFilter = new OptionFilter($wid);	
        $this->sortingFilter = new SortingFilter($wid);
        
        $this->productFactory = new ProductFactory($wid);	
        //$this->widgetFactory = new WidgetFactory($wid);

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
        return $this->optionFilter->setOptions($array);
    }
    
    public function setSortOrder($string) {
        return $this->sortingFilter->setSortOrder($string);
    }
    
    public function setSortBy($sortBy){
        return $this->sortingFilter->setSortBy($sortBy);
    }
    
    public function setMinOptions($array){
        foreach ($array as $key=>$val) {
            $_GET['min_'.$key] = $val;
        }
        return $this->optionFilter->setMinOptions($array);
    }
        
    public function setEqOptions($array){
        
        return $this->optionFilter->setEqOptions($array);
    }
        
    public function setMaxOptions($array){
        foreach ($array as $key=>$val) {
            $_GET['max_'.$key] = $val;
        }
        return $this->optionFilter->setMaxOptions($array);
    }

	public function setStringFilter(StringFilter $newFilter)
	{
		$this->stringFilter = $newFilter;
	}

    public function setProductFactory(ProductIdHandler $newFactory)
    {
        $this->productFactory = $newFactory;
    }

    public function setWidgetFactory(WidgetFactory $newFactory)
    {
        $this->widgetFactory = $newFactory;
    }
    
	function getResults(array $product_ids)
	{
	    
	    $stringFilterTimer = new IdHandleTimer($this->stringFilter);
		$product_ids = $stringFilterTimer->getResults($product_ids);
     
        //$widgetFactoryTimer = new IdHandleTimer($this->widgetFactory);
      
       // if(!$this->results_only){
        //    $this->searchresult->widgets = $widgetFactoryTimer->getResults($product_ids);
       // }
        
        $optionFilterTimer = new IdHandleTimer($this->optionFilter);
        $filtered_ids = $optionFilterTimer->getResults($product_ids);
      
        $this->searchresult->number_of_products  = sizeof( $filtered_ids);
        
        $sortingTimer = new IdHandleTimer($this->sortingFilter);
        $sorted_ids = $sortingTimer->getResults($filtered_ids);
        if(!$this->showall){
            // We only take the offset plus 50 if not showing all
            $pperp = Settings::get('max_search_results', $this->wid); 
            $sorted_ids = array_slice($sorted_ids, $this->search_offset, $pperp);
        
        } 
        
        $this->searchresult->option_filter_time = $optionFilterTimer->getTime();
        $this->searchresult->sorting_time = $sortingTimer->getTime();
       // $this->searchresult->widget_factory_time = $widgetFactoryTimer->getTime();
        $this->searchresult->string_filter_timer = $stringFilterTimer->getTime();
        
        $productFactoryTimer = new IdHandleTimer($this->productFactory);
        $this->searchresult->products = $productFactoryTimer->getResults($sorted_ids);
        $this->searchresult->product_factory_time = $productFactoryTimer->getTime();
        
        $this->searchresult->synonyms_corrected_to = $this->stringFilter->get_words_synonyms_corrected();
        $this->searchresult->spelling_corrected_to = $this->stringFilter->get_words_spelling_corrected();
        $this->searchresult->searchwords = $this->stringFilter->get_words_i_search_for();
        $this->searchresult->original = $this->stringFilter->get_original(); 
        $this->searchresult->did_you_mean = $this->stringFilter->get_did_you_mean();
        $this->searchresult->related_searches = $this->stringFilter->get_related_searches();
        
        return ($this->searchresult);
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
	
	public function errors() {
		$this->errors = array();

		foreach ($this->userdefinedkeywords_search->errors as $err) {
            $this->errors[] = $err;
        }
        foreach ($this->stringFilter->errors as $err) {
            $this->errors[] = $err;
        }
		
		return $this->errors;
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

