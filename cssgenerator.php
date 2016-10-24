<?php
class CSSGenerator  {
    private $wid; 
    
    public function __construct($wid) {
        $this->wid = $wid;
    }
	
	public function generateSuggestion() {
		return $this->generate(false);
	}
    
    public function generate($onlyResult) {
    		$db = VPDO::getVdo(DB_PREFIX.$this->wid);
            $sql = 'SELECT template_id,attributes FROM search_templates WHERE active = 1';
            $active_template = $db->getTableList($sql);
            $active_template = $active_template[0];
            $this->attributes = json_decode($active_template->attributes,TRUE);
			
        	$tool_bar_width = settings::get('sidebar_width',$this->wid);
            $suggestions = settings::get('suggestions',$this->wid);
            $suggestionHeight = settings::get('suggestion_height',$this->wid);
            $suggestionWidth = settings::get('suggestion_width',$this->wid);
            $frameOnHover = (int)Settings::get('frame_on_product_hover',$this->wid);
            $smallMargin = 5;
            $toolbar_max_height = settings::get('toolbar_max_height',$this->wid);;
            
            return "
                #vubla {
                     margin-top: 5px;
                }
                
                
                #vubla a:hover {text-decoration: none; font-weight: bold; color: " . $this->attributes['Focused Link Color'] . ";}
                #vubla a.vbl-product-name:hover {
                    text-decoration: none; font-weight: bold; color: " . $this->attributes['Focused Link Color'] . ";
                    ".Settings::get('extra_hover_name_style',$this->wid)."
                }
                #vubla a:link {text-decoration: none; font-weight: bold; color: " . $this->attributes['Link Color'] . ";}
                #vubla a:visited {text-decoration: none; font-weight: bold; color: " . $this->attributes['Visited Link Color'] . ";}
                #vubla a:active {text-decoration: none; font-weight: bold; color: " . $this->attributes['Focused Link Color'] . ";}
                #vubla input[type=\"checkbox\"] {border:0px;}
                #vubla .vbl-product-img {border:0px;}
                
                #vbl-toolbar {
                    min-height: ".$toolbar_max_height."px;
                    max-height: ".$toolbar_max_height."px;
                    font-family: " . $this->attributes['Font Stack'] . ";
                    color: " . $this->attributes['Text Color'] . ";
                    overflow: hidden;   

                    padding: 10px;
                    
                    margin-bottom: 20px;
                    
                    font-size: 14px;
                    
                    border-top: 1px solid " . $this->attributes['Secondary Color'] . ";
                    border-bottom: 1px solid " . $this->attributes['Secondary Color'] . ";
                    
                
                }
                
                #vbl-toolbar-msg {
                    float: left;
                }
                
                #vbl-toolbar-search {
                      width: 315px;

                    float: right;
                    
                    font-size: 10px;
                }

        #vbl-toolbar-search input[type=text] {
            width: 250px;

            padding: 4px;

            font-size: 14px;

            border: 2px solid " . $this->attributes['Secondary Color'] . ";
        }

        #vbl-toolbar-search input[type=submit] {
            padding: 4px;
            width: 45px;
            font-size: 14px;
        }

        #vbl-toolbar-sort {
            position: relative;
            top: 10px;          
        }

        #vbl-show-all {
        }
                    
                #vbl-sidebar {
                    min-height: 500px;
                    width: ".$tool_bar_width."%;
                    margin-right: 0.5%;
                    margin-left: 0.5%;
                    float: left;
                    font-family: " . $this->attributes['Font Stack'] . ";
                    color: " . $this->attributes['Text Color'] . ";
                    font-size: " . (9*($this->attributes['Font Size']/100)+9) . "px;
                }
                
                .vbl-sidebar-widget {
                    min-height: 100px;
                    
                    margin-bottom: 10px;                    
                    
                    padding: 10px;
                    
                    background: #ffffff; /* Old browsers */
                    background: -moz-linear-gradient(top,  #ffffff 0%, #ededed 0%, #f7f6f6 100%); /* FF3.6+ */
                    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ffffff), color-stop(1%,#ededed), color-stop(100%,#f7f6f6)); /* Chrome,Safari4+ */
                    background: -webkit-linear-gradient(top,  #ffffff 0%,#ededed 0%,#f7f6f6 100%); /* Chrome10+,Safari5.1+ */
                    background: -o-linear-gradient(top,  #ffffff 0%,#ededed 0%,#f7f6f6 100%); /* Opera 11.10+ */
                    background: -ms-linear-gradient(top,  #ffffff 0%,#ededed 0%,#f7f6f6 100%); /* IE10+ */
                    background: linear-gradient(top,  #ffffff 0%,#ededed 0%,#f7f6f6 100%); /* W3C */
                    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#f7f6f6',GradientType=0 ); /* IE6-9 */

                    border: 1px solid #cfcece;
                    
                    -moz-border-radius: 5px;
                    -webkit-border-radius: 5px;
                    border-radius: 5px;
                    
                    -moz-box-shadow: 0px 1px 1px rgba(0,0,0,0.2);
                    -webkit-box-shadow: 0px 1px 1px rgba(0,0,0,0.2);
                box-shadow: 0px 1px 1px rgba(0,0,0,0.2);
                }

                .vbl-category {
                    margin: 0 auto;
                    display: block;
                    height: 20px;
                }
                
                .vbl-parent {
                    
                }
               
                .vbl-child {
                    padding-left: 15px;
                }
                
                #vbl-content-wrapper {
                    width: " . ($onlyResult ? "100%;" : 99-$tool_bar_width."%;") ."
                    float: right;
                }
                
                #vbl-footer {
                    padding: 10px;
                    
                    font-size: " . (6*($this->attributes['Font Size']/100)+12) . "px;
                    font-family: " . $this->attributes['Font Stack'] . ";
                }
                
                #vbl-footer-products {
                    float: left;
                }
                
                #vbl-footer-pages {
                    float: right;
                }

                #vbl-no-result {
                    padding: 10px;
            
                    background: #ffffff;
            
                    //border: 1px solid #dddcdc;
            
                    -webkit-border-radius: 3px;
                    -moz-border-radius: 3px;
                    border-radius: 3px;
                }
                
                #vbl-no-result-headline {
                    margin-bottom: 20px;
            
                    font-family: Verdana, Tahoma, Arial;
                    font-size: 18px;
                    font-weight: bold;
                }
            
                #vbl-no-result-box {
                    height: 110px;
                    width:500px;
                    font-family: Verdana, Tahoma, Arial;
                    font-size: " . (6*($this->attributes['Font Size']/100)+12) . "px;
                }
            
                #vbl-no-result-text {
                    height: 90px;
                    width:348px;
                    float: left;
                    padding: 10px 40px 10px 10px;
                    background: url(\"http://api.vubla.com/images/bg.png\");
                    background-repeat: no-repeat;
                }
            
                #vbl-photo {
                    float: left;
                }
                
                #vbl-wait {
                    background: url(\"http://api.vubla.com/images/ajax-loader.gif\") repeat scroll 0 0;
                    display: none;
                    max-width: 220px;
                    height: 20px;
                    position: float;
                
                }
                .slider-from, .slider-to{
                    font-weight: bold;
                }
                
                .vbl-product-sku {
                    color: #7F7F7F;
                    font-size: 11px; 
                 }
                        
                .vbl-moms {
                        color: #6B6B6B !important;
                        font-size: 11px !important;
                        font-weight: normal !important;
                    
                }
                      
                #vbl-suggestions {
                    height: ".(($suggestionHeight+$smallMargin)*$suggestions)."px;
                    width: ".($suggestionWidth)."px;
                    position: absolute;
                    z-index: 1000;
                    padding: ".$smallMargin."px;
                    margin-top: -1px;
                    text-align: left;
                    background: white;
                    -moz-box-shadow: 2px 2px 0px rgba(0,0,0,0.36);
                    -webkit-box-shadow: 2px 2px 0px rgba(0,0,0,0.36);
                    box-shadow: 2px 2px 3px rgba(0,0,0,0.60);
                    border: 1px solid #CCC;
                    overflow-x: hidden;
                    overflow-y: hidden;
                    font-family: " . $this->attributes['Font Stack'] . ";
                    color: " . $this->attributes['Text Color'] . ";
                    display: none;
               }

               .vbl-suggestion {
                    height: ".($suggestionHeight)."px;
                    width: ".$suggestionWidth."px;
                    margin-bottom: ".($smallMargin-2)."px;".
                    ($frameOnHover?'
                    border: 1px solid white;':'
                    border-bottom: 1px solid '. $this->attributes['Secondary Color'] . ';
                    border-top: 1px solid white;
                    border-left: 1px solid white;
                    border-right: 1px solid white;'
                    )."
               }
               
               .vbl-suggestion:hover { " .
                    ($frameOnHover?('
                    border-color: '. $this->attributes['Secondary Color'] .';'):
                    ''
                    )."
                    cursor: pointer;
               }
               
               .vbl-suggestion:focus { 
                    border-color: ". $this->attributes['Secondary Color'] .";
               }

               .vbl-suggestion div:hover {
                    text-decoration: none; 
                    color: " . $this->attributes['Focused Link Color'] . ";
                    ".Settings::get('extra_hover_name_style',$this->wid)."
               }
                
               .vbl-suggestion-image-surrounder {
                   display: inline-block;
                   width: ".$suggestionHeight."px; 
                   height: ".$suggestionHeight."px;
                    vertical-align: top;
               }
               
               .vbl-suggestion-centerring-wrapper {
                    display: table;
                    margin: auto;
                }

               .vbl-suggestion-image {
                   display: table-cell;
                   vertical-align: middle;
                   margin: auto;
                   max-width: ".$suggestionHeight."px; 
                   max-height: ".$suggestionHeight."px;
               }

               .vbl-suggestion-text {
                    display: inline-block;
                    width: ".($suggestionWidth-$suggestionHeight-10)."px; 
                    height: ".$suggestionHeight."px;
                    margin-left: 5px; 
               }

               .vbl-suggestion-title {
                    //font-weight:bold;
                    font-size: 12px;
               }

               .vbl-suggestion-description {
                    font-size: 10px;
               }

        /*
         * jQuery UI CSS Framework 1.8.16 (Vubla modified)
         *
         * Copyright 2011, AUTHORS.txt (http://jqueryui.com/about)
         * Dual licensed under the MIT or GPL Version 2 licenses.
         * http://jquery.org/license
         *
         * http://docs.jquery.com/UI/Theming/API
         */


        /* Component containers
        ----------------------------------*/
        #vubla .ui-widget { font-family: Trebuchet MS, Tahoma, Verdana, Arial, sans-serif; font-size: 1.1em; }
        #vubla .ui-widget .ui-widget { font-size: 1em; }
        #vubla .ui-widget input, #vubla .ui-widget select, #vubla .ui-widget textarea, #vubla .ui-widget button { font-family: Trebuchet MS, Tahoma, Verdana, Arial, sans-serif; font-size: 1em; }
        #vubla .ui-widget-content { border: 1px solid #dddddd; background: #eeeeee 50% top repeat-x; color: #333333; }
        #vubla .ui-widget-content a { color: #333333; }
        #vubla .ui-widget-header { border: 0px; background: " . $this->attributes['Primary Color'] . " 50% 50% repeat-x; color: #ffffff; font-weight: bold; }
        #vubla .ui-widget-header a { color: #ffffff; }

        /* Interaction states
        ----------------------------------*/
        #vubla .ui-state-default, #vubla .ui-widget-content .ui-state-default, #vubla .ui-widget-header .ui-state-default { border: 1px solid #cccccc; background: #f6f6f6 50% 50% repeat-x; font-weight: bold; color: #1c94c4; }
        #vubla .ui-state-default a, #vubla .ui-state-default a:link, #vubla .ui-state-default a:visited { color: #1c94c4; text-decoration: none; }
        #vubla .ui-state-hover, #vubla .ui-widget-content .ui-state-hover, #vubla .ui-widget-header .ui-state-hover, #vubla .ui-state-focus, #vubla .ui-widget-content .ui-state-focus, #vubla .ui-widget-header .ui-state-focus { border: 1px solid #fbcb09; background: #fdf5ce 50% 50% repeat-x; font-weight: bold; color: #c77405; }
        #vubla .ui-state-hover a, #vubla .ui-state-hover a:hover { color: #c77405; text-decoration: none; }
        #vubla .ui-state-active, #vubla .ui-widget-content .ui-state-active, #vubla .ui-widget-header .ui-state-active { border: 1px solid #fbd850; background: #ffffff 50% 50% repeat-x; font-weight: bold; color: #eb8f00; }
        #vubla .ui-state-active a, #vubla .ui-state-active a:link, #vubla .ui-state-active a:visited { color: #eb8f00; text-decoration: none; }
        #vubla .ui-widget :active { outline: none; }

        /* Interaction Cues
        ----------------------------------*/
        #vubla .ui-state-highlight, #vubla .ui-widget-content .ui-state-highlight, #vubla .ui-widget-header .ui-state-highlight  {border: 1px solid #fed22f; background: #ffe45c 50% top repeat-x; color: #363636; }
        #vubla .ui-state-highlight a, #vubla .ui-widget-content .ui-state-highlight a, #vubla .ui-widget-header .ui-state-highlight a { color: #363636; }
        #vubla .ui-state-error, #vubla .ui-widget-content .ui-state-error, #vubla .ui-widget-header .ui-state-error {border: 1px solid #cd0a0a; background: #b81900 50% 50% repeat; color: #ffffff; }
        #vubla .ui-state-error a, #vubla .ui-widget-content .ui-state-error a, #vubla .ui-widget-header .ui-state-error a { color: #ffffff; }
        #vubla .ui-state-error-text, #vubla .ui-widget-content .ui-state-error-text, #vubla .ui-widget-header .ui-state-error-text { color: #ffffff; }
        #vubla .ui-priority-primary, #vubla .ui-widget-content .ui-priority-primary, #vubla .ui-widget-header .ui-priority-primary { font-weight: bold; }
        #vubla .ui-priority-secondary, #vubla .ui-widget-content .ui-priority-secondary,  #vubla .ui-widget-header .ui-priority-secondary { opacity: .7; filter:Alpha(Opacity=70); font-weight: normal; }
        #vubla .ui-state-disabled, #vubla .ui-widget-content .ui-state-disabled, #vubla .ui-widget-header .ui-state-disabled { opacity: .35; filter:Alpha(Opacity=35); background-image: none; }

        /* Misc visuals
        ----------------------------------*/

        /* Corner radius */
        #vubla .ui-corner-all, #vubla .ui-corner-top, #vubla .ui-corner-left, #vubla .ui-corner-tl { -moz-border-radius-topleft: 4px; -webkit-border-top-left-radius: 4px; -khtml-border-top-left-radius: 4px; border-top-left-radius: 4px; }
        #vubla .ui-corner-all, #vubla .ui-corner-top, #vubla .ui-corner-right, #vubla .ui-corner-tr { -moz-border-radius-topright: 4px; -webkit-border-top-right-radius: 4px; -khtml-border-top-right-radius: 4px; border-top-right-radius: 4px; }
        #vubla .ui-corner-all, #vubla .ui-corner-bottom, #vubla .ui-corner-left, #vubla .ui-corner-bl { -moz-border-radius-bottomleft: 4px; -webkit-border-bottom-left-radius: 4px; -khtml-border-bottom-left-radius: 4px; border-bottom-left-radius: 4px; }
        #vubla .ui-corner-all, #vubla .ui-corner-bottom, #vubla .ui-corner-right, #vubla .ui-corner-br { -moz-border-radius-bottomright: 4px; -webkit-border-bottom-right-radius: 4px; -khtml-border-bottom-right-radius: 4px; border-bottom-right-radius: 4px; }

        /* Overlays */
        #vubla .ui-widget-overlay { background: #666666 50% 50% repeat; opacity: .50;filter:Alpha(Opacity=50); }
        #vubla .ui-widget-shadow { margin: -5px 0 0 -5px; padding: 5px; background: #000000 50% 50% repeat-x; opacity: .20;filter:Alpha(Opacity=20); -moz-border-radius: 5px; -khtml-border-radius: 5px; -webkit-border-radius: 5px; border-radius: 5px; }
        /*
         * jQuery UI Slider 1.8.16
         *
         * Copyright 2011, AUTHORS.txt (http://jqueryui.com/about)
         * Dual licensed under the MIT or GPL Version 2 licenses.
         * http://jquery.org/license
         *
         * http://docs.jquery.com/UI/Slider#theming
         */
        .ui-slider { position: relative; text-align: left; }
        .ui-slider .ui-slider-handle { position: absolute; z-index: 2; width: 1.2em; height: 1.2em; cursor: default; }
        .ui-slider .ui-slider-range { position: absolute; z-index: 1; font-size: .7em; display: block; border: 0; background-position: 0 0; }

        .ui-slider-horizontal { height: .8em; }
        .ui-slider-horizontal .ui-slider-handle { top: -.3em; margin-left: -.6em; }
        .ui-slider-horizontal .ui-slider-range { top: 0; height: 100%; }
        .ui-slider-horizontal .ui-slider-range-min { left: 0; }
        .ui-slider-horizontal .ui-slider-range-max { right: 0; }

        .ui-slider-vertical { width: .8em; height: 100px; }
        .ui-slider-vertical .ui-slider-handle { left: -.3em; margin-left: 0; margin-bottom: -.6em; }
        .ui-slider-vertical .ui-slider-range { left: 0; width: 100%; }
        .ui-slider-vertical .ui-slider-range-min { bottom: 0; }
        .ui-slider-vertical .ui-slider-range-max { top: 0; }";
                
    }
}
