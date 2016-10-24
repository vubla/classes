<?php 
    
/**
 * SearchResult
 * 
 * "Dumb" class containing a search result and its related info.
 */
class SearchResult {
    
    /**
     *
     * @var Array[stdClass]
     */
    public $products = array();
    
    /**
     *
     * @var array 
     */
    public $widgets = array();
    
    /**
     *
     * @var int
     */
    public $number_of_products = 0;
    public $synonym_corrected_to = array();
    public $spelling_corrected_to = array();
    public $did_you_mean = array();
    public $searchwords = array();
    public $original;
    public $option_filter_time = 0;
    public $sorting_time = 0;
    public $widget_factory_time = 0;
    public $string_filter_timer = 0;
    public $total_search_time = 0;
    public $product_factory_time = 0; 
    public $userdefinedkeywords = array();
    public $related_searches = array();
    public $display_logo = 0;
}

