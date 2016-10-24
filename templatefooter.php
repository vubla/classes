<?php

class TemplateFooter extends BaseTemplateObject {
    
   
    
    function __construct ($wid,SearchResult $search)
    {
      parent::__construct($wid,$search);  
      
    }
    
    function generateHtml(){
        if($this->searchResult->number_of_products < Settings::get('max_search_results', $this->wid) || $this->showall)
        {
            return null;    
        }
        $footer = "<div id=\"vbl-footer\">\n";
        $footer .= "<div id=\"vbl-footer-products\">\n";
      //  $footer .= "Viser <b>".$this->offset*Settings::get('max_search_results')."-". ceil(Settings::get('max_search_results')*(1+$this->offset))."</b> af <b>".$sr->number_of_products."</b> produkter";
        $footer .= "Viser <b>".$this->getFrom()."-". $this->getTo()."</b> af <b>".$this->getTotal()."</b> produkter";
     
        $footer .= "\n</div>\n";
        $footer .= "<div id=\"vbl-footer-pages\">\n";
        foreach($this->getPagesList() as $elem)
        {
          
            if($elem->marked)
            {
                $footer .= ' ' .$elem->number.'';
            }
             else 
            {
                $footer .= " <a class=\"vbl-page-link\" href=\"".$this->getShopLink(array('search_offset'=>$elem->offset))."\">".$elem->number."</a>"; 
            } 
        }
        $footer .= "\n</div>\n";
        $footer .= "\n</div>\n";
        return $footer;
    }
    
    function getFrom()
    {
        
        $var = $this->search_offset + 1;
        if($this->search_offset > $this->searchResult->number_of_products)
        {
            $var = $this->searchResult->number_of_products;  
        }
        return $var; 
    }
    
    function getTo()
    {
        $var = $this->search_offset + settings::get('max_search_results', $this->wid);
        if($var > $this->searchResult->number_of_products)
        {
            $var = $this->searchResult->number_of_products;
        }
        return $var;
    }
    
    function getTotal()
    {
        return $this->searchResult->number_of_products;
    }
    
    function getPagesList()
    {
        $pperp = settings::get('max_search_results', $this->wid);
        $list = array();
        for($i = 0; $i < ceil($this->getTotal()/$pperp); $i++)
        {
            $elem = new StdClass();
            $elem->offset = $i * $pperp;
            $elem->number = $i + 1;
            $elem->marked = $this->search_offset >= $elem->offset && $this->search_offset < $elem->offset + $pperp;
            $list[$i] = $elem;
        }
        return $list;
    }
    
}


















