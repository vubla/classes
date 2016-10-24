<?php

class SliderWidget extends Widget {
    
    public $min;
    public $max;
    public $selected_min;
    public $selected_max;
    public $options = array();
   
    function __construct($wid,$id){
        parent::__construct($wid,$id);
    }
    
    function generateHtml(){
        if(!$this->selected_min){
           $this->selected_min =  floor($this->min);
        }
         if(!$this->selected_max){
           $this->selected_max =  ceil($this->max);
        }
         
        $slider_active_identifier  = $this->id."_slider_active";
   		return "<div id=\"vbl-widget-" . $this->id . "\" class=\"vbl-sidebar-widget\">
   					<b>" . ucfirst(Text::_($this->id)) . "</b><br />
   					<br />
   					Fra
   				     <input type=\"text\" id=\"vbl-".$this->id."-from\" name=\"min_options[".$this->id."]\" value=\"$this->selected_min\"/>
                     <input type=\"hidden\" id=\"vbl-".$this->id."-bottom\" name=\"min_".$this->id."_bottom\" value=\"$this->min\"/>
   					 <span >
   					    <b>".number_format($this->min,0,',','.')."</b>
   					 </span> til 
   					    <input type=\"text\" id=\"vbl-".$this->id."-to\" name=\"max_options[".$this->id."]\" value=\"$this->selected_max\"/>
                        <input type=\"hidden\" id=\"vbl-".$this->id."-top\" name=\"max_".$this->id."_top\" value=\"$this->max\"/>
   					<span >
   					    <b>".number_format($this->max,0,',','.')."</b>
   					</span> DKK<br /> 
						<br />				   	
				   	<div id=\"vbl-widget-" . $this->id . "-slider\"></div>	<br />
				    
				  
				   Valgt fra <span class=\"slider-from\">".number_format($this->selected_min,0,',','.')."</span> til
				   <span  class=\"slider-to\">".number_format($this->selected_max,0,',','.')."</span> DKK  
				   
				   <div style=\"margin-top:5px;margin-bottom:10px;\">
    				   <div  style=\"display:inline-block\">
                           <a id=\"vbl-".$this->id."-slider-reset\" href=\"".
                           $this->getShopLink(array($slider_active_identifier=>'no',"ie"=>"ISO-8859-1"))."\">
                           Nulstil
                           </a>
                       </div>
    				   <div class=\"vbl-remove-with-js\" style=\"display:inline-block;margin:auto;text-align:right;right:0px;float:right\"><input type=\"submit\" value=\" Opdater \"/>
    				   </div>
    			   </div>
				   
				
				   <input type=\"hidden\" id=\"vbl-".$this->id."-slider-active\" name=\"".$slider_active_identifier."\" value=\"".$this->$slider_active_identifier."\"/>
				   
   		</div>"; 
    }
    
    function generateJS() {
        $slider_active_identifier  = "slider_active_".$this->id;
         return "
                
                var min_".$this->id." = " . $this->min . ";
                var max_".$this->id." = " . $this->max . ";
                var min_".$this->id."_selected = " . $this->selected_min . ";
                var max_".$this->id."_selected = " . $this->selected_max . ";
                var ".$this->id."_active_slider = ".(int)($this->$slider_active_identifier != 'yes').";
                
                temp = jQuery('#vbl-".$this->id."-slider-active').val();
                if(temp != null)
                {
                    ".$this->id."_active_slider = (temp == 'yes');
                }
                
                jQuery('#vbl-".$this->id."-from').hide();
				jQuery('#vbl-".$this->id."-to').hide();  
                if(!".$this->id."_active_slider) {
                    jQuery('#vbl-".$this->id."-from').attr('disabled','disabled');
                    jQuery('#vbl-".$this->id."-to').attr('disabled','disabled');
                } else {
                    jQuery('#vbl-".$this->id."-slider-active').val('yes');
                }
                jQuery('#vbl-".$this->id."-slider-reset').click( function vblResetSlider(nme){
                   jQuery('#vbl-".$this->id."-from').attr('disabled','disabled');
                   jQuery('#vbl-".$this->id."-to').attr('disabled','disabled');
                   jQuery('#vbl-".$this->id."-slider-active').val('no');
                    //return false;
                });
                
                temp = parseInt(jQuery('#vbl-".$this->id."-bottom').val());
                if(temp != null)
                {
                    min_".$this->id." = temp;
                }
                temp = parseInt(jQuery('#vbl-".$this->id."-top').val());
                if(temp != null)
                {
                    max_".$this->id." = temp;
                }
                
                if(jQuery('#vbl-" . $this->id."-slider-active').val() == 'yes') {
                    temp = parseInt(jQuery('#vbl-".$this->id."-from').val());
                    if(temp != null)
                    {
                        min_".$this->id."_selected = temp;
                    }
                    temp = parseInt(jQuery('#vbl-".$this->id."-to').val());
                    if(temp != null)
                    {
                        max_".$this->id."_selected = temp;
                    }
                } else {
                    min_".$this->id."_selected = min_".$this->id.";
                    max_".$this->id."_selected = max_".$this->id.";
                }
              
              function vbladdDots(str) {
    			var amount = new String(str);
    			amount = amount.split(\"\").reverse();

    			var output = \"\";
    			for ( var i = 0; i <= amount.length-1; i++ ){
       				output = amount[i] + output;
        			if ((i+1) % 3 == 0 && (amount.length-1) !== i)output = '.' + output;
    			}
   				 return output;
			  }
			  
		      jQuery('#vbl-widget-" . $this->id . "-slider').slider({
                    range: true,
                    min: min_".$this->id.",
                    max: max_".$this->id.",
            
                    values: [min_" . $this->id."_selected, max_" . $this->id."_selected],
                    slide: function( event, ui ) {
                        jQuery('#vbl-widget-" . $this->id." .slider-from').text(vbladdDots(ui.values[0]));
                        jQuery('#vbl-widget-" . $this->id." .slider-to').text(vbladdDots(ui.values[1]));
                        jQuery('#vbl-".$this->id."-from').val(ui.values[0]);
                        jQuery('#vbl-".$this->id."-to').val(ui.values[1]);
                        jQuery('#vbl-".$this->id."-from').removeAttr('disabled');
                        jQuery('#vbl-".$this->id."-to').removeAttr('disabled');
                        jQuery('#vbl-".$this->id."-slider-active').val('yes');
                    }
                });
                
				jQuery('#vbl-widget-".$this->id."-slider')
                        .bind( 'slidechange', function(event, ui) {
                            smallSearch();
                        });
				
				";
            //  return $js;
    }
}



?>
