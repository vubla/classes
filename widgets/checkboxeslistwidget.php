<?php

class CheckboxeslistWidget extends ListWidget {
    
    public $options;
    
    function __construct($wid,$id){
        parent::__construct($wid,$id);
    }
    
    function generateHtml(){
        
        
        $out = "<div id=\"vbl-widget-" . $this->id . "\" class=\"vbl-sidebar-widget\">";
        $out .= "<b>" . Text::_($this->id) . "</b><br /><br />";
        sort($this->options, SORT_NUMERIC);
        foreach ($this->options as  $name) {
             //var_dump($this->eq_option['category']);    
             $checked_parent = ( isset($this->eq_option[$this->id]) && in_array($name,$this->eq_option[$this->id] )) ? 'checked' : '';
       //      var_dump($node->name.' ' . print_r($this->eq_option['category'],true) );
        //     echo $checked_parent;
             $out .= ' <div class="vbl-category">';
             $out .=  '<input type="checkbox" name="eq_options['.$this->id.'][]" value="'.$name.'" '.$checked_parent.'/> '.$name.'<br />';
             $out .= '</div>';
             
            
             
        }
      //  exit;
        $out .= "</div>";
        return $out;
        // Here you can also use $this->NAME OF SOME OPTION
      return 'Some check box list with id' . $this->id;
     
    }
    
    function generateJS() {
        return '';
    }
   
   function generateRefreshJS() {
       return '';
   }
    
}



?>