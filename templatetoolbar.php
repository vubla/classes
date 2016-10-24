<?php

class TemplateToolbar extends BaseTemplateObject {
    

    
    function __construct ($wid, SearchResult $search)
    {
        parent::__construct($wid,$search);  
      
    }
    
    public function generateHtml() {

        $toolbar = "<div id=\"vbl-toolbar\">\n";
        // There should also be a input field in the search layout which displays the key words(Going to be in the top bar) 
        // We need a form. We do it without JS(initially)
        // Price slider should be two text fields if JS is disabled. //OCH ALLT DÄREMELLAN
        
        $toolbar .= "<div id=\"vbl-toolbar-msg\">\n".$this->generateMsg()."<br />\n";
    
       
        foreach($this->searchResult->userdefinedkeywords as $userdefinedkeyword) {
            $toolbar .= "<a href=\"$userdefinedkeyword->url\">$userdefinedkeyword->text</a> ";
        }
     
        if(is_null($this->param)){
            $this->param = 'keywords';
        }
        
        $toolbar .= "\n</div>\n";
        $toolbar .= "\n<div id=\"vbl-toolbar-search\">\n";
	    $toolbar .= '   <div>
		    				<input id="vbl-search-field" type="text" name="'.$this->param.'"/ value="'.$this->searchResult->original.'"/>
		    				
							<input type="submit" value=" Søg "/>
						</div> 
						<input type="hidden" name="previous"/ value="'.$this->searchResult->original.'"/>
						<div id="vbl-toolbar-sort">
							<b>Sorter efter: </b>
							<select id="vbl-sort-by" name="sort_by">
							<option value="">Relevans</option>'."\n";
        foreach(OptionHandler::getSortableOptions($this->wid) as $sortable){
            $toolbar .= '<option value="'.$sortable->name.'@'.$sortable->order_by.'" '.(($sortable->name == $this->sort_by || $sortable->name.'@'.$sortable->order_by == $this->sort_by)? 'selected' : null) .'>'.Text::_($sortable->name).', '.Text::_($sortable->order_by).'</option>'."\n";
        }
        $toolbar .= '</select>'."\n";
    
        if($this->searchResult->number_of_products <= settings::get('max_search_results', $this->wid))
        {
            $style = "style=\"visibility:hidden;\"";
        } 
        else 
        {
            $style = "";
        }
       
        $toolbar .= '<input id="vbl-show-all" '.$style.' type="checkbox" name="showall" value="Vis alle" '.($this->showall? ' checked="yes" ':'') .'/>';
       
      
            $toolbar .= '<b '.$style.'> Vis alle</b></div>'."\n";
       
     
        $toolbar .= "</div>\n";
        $toolbar .= "</div>\n";
        
        
        return $toolbar;    
        
   } 
    function generateMsg(){
         //   $search = $this->searchResult;
            
            /*
             * This is removed, for now. The property does not exist any more. 
            if($search->show_words_you_think_i_search_for ) {
                $msg_nothing_found = 'Vi fandt ikke noget p&aring;: <strong>'. $search->original . '</strong> vi s&oslash;ger derfor p&aring;: <strong>'.implode(" ", $search->synonyms_corrected_to).'</strong><br />'.PHP_EOL;
            }
            */
            /*
             * Where should user defined keywords be displayed?
            if(isset($search->userdefinedkeywords)) {
                $userdefinedkeywords = $search->userdefinedkeywords;
            } else {
                $userdefinedkeywords = array();
            }
            if($userdefinedkeywords == null) {
                $userdefinedkeywords = array();
            }
            */
            
            //##########MESSAGES##########//
           

            if($this->searchResult->number_of_products == 1) { 
                $the_word_products = "produkt";
            }
            else {
                $the_word_products = "produkter";
            }

            $msg = 'Vi fandt <b>'.$this->searchResult->number_of_products.'</b> '.$the_word_products." for <b>'".$this->searchResult->original."'</b>";
           
            
            $msg .= "<br />";
            $msg .= '<span id="vbl-did-you-mean" class="vbl-hide-on-load">';
            foreach($this->searchResult->did_you_mean as $array){
                $words = implode(' ', $array);
                if(is_null($this->param)){
                    $param = 'keywords';
                } else {
                     $param = $this->param;
                }
                       $msg .= "<br /><a id=\"vbl-did-you-mean-link\" href=".$this->getShopLink(array($param=>urlencode(iconv('UTF-8','ISO8859-1',$words)))).">Mente du '" . $words . '\'?</a>';
                         $msg .= '<input id="vbl-did-you-mean-words" type="hidden" name="vbl-did-you-mean-words" value="'.$words.'">';
            }
            
            
 //           throw new Exception("Not implemented");
            if(count($this->searchResult->products) == 0 && !empty($this->searchResult->related_searches)){
                  $msg .= "<br />Vi fandt ikke nogen resultater på: ". $this->searchResult->original.", men vi fandt noget på: ";
                  $rs = array();
                  foreach($this->searchResult->related_searches as $relSearch)
                  {
                       $rs[] = "<a id=\"vbl-did-you-mean-link\" href=".$this->getShopLink(array($param=>urlencode(iconv('UTF-8','ISO8859-1',$relSearch->word)))).">" .  $relSearch->word . '(' . $relSearch->products . ')'. '</a>';
                      
                  }
                  $msg .= implode(', ', $rs);
            }
           
             $msg .= '</span>';
            
            
            /* also removed
            $msg = $msg_nothing_found . "<br /><br />" . 'Vi fandt <b>'.$search->number_of_products.'</b> '.$the_word_products.' for <b>"'. implode(" ", $search->words_you_think_im_searching_for).'"</b>';
            }
            elseif(!empty($msg_did_you_mean)) {
                $msg = $msg . "<br /><br />" . $msg_did_you_mean;
            }*/
            return $msg;
       }
    
}


