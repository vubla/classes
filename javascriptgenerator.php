<?php

    class JavaScriptGenerator extends AnySearchObject {
        private $wid;
        private $test;
        
        public function __construct($wid,$test = false) {
            $this->wid = $wid;
            $this->test = $test;
        }
        
        public function generate($widgets = array()) {
            if(!is_array($widgets)) {
                throw new VublaException('Input to generate is expected to be an array of widgets, but was not an array.');
            }
            foreach ($widgets as $widget) {
                if(!($widget instanceof Widget)) {
                    throw new VublaException('Input to generate is expected to be an array of widgets, but the element "' . print_r($widget,true) . '" was not a widget.');
                } 
            }
            $generatedTemplate = $this->generateGetJQueryAndGoOnLoad().
                 $this->generateDump(). //use this to dump a variable like php var_dump
            'getJQueryAndGo(function (j) { ' .
            $this->generateVariables().'
            var jQuery = j;
            '.
                    $this->generateJQueryUI().'
                      /// document.write("<scr" + "ipt src=\"https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js\" type=\"text/javascript\"></scr" + "ipt>");
                      
                      //loadToHead("script","https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.min.js",function(){
                      function RunOnRefresh() {
                          var fullSearch  = AddStdVariables;
                          var smallSearch = AddStdVariables;
                    ';
                  
            foreach($widgets as $widget) {
                $generatedTemplate .= $widget->generateJS();
            }
            $bindingsIdents = explode(',',Settings::get('search_form_identifiers',$this->wid));
            $additionalBindings = '';
            foreach ($bindingsIdents as $binding) {
                if($binding) {
                    $additionalBindings .= '
                           jQuery(\''.$binding.'\')
                           .submit(function () {
                               var q = jQuery(\''.$binding.' input:text\').val();
                               jQuery("#vbl-search-field").val(q);
                               return fullSearch();
                           });
                           ';
                }
            }
            
            $generatedTemplate .= '
            
                        if (jQuery.support.cors) { // We can make AJAX searches (not in IE8)
                            var fullSearch  = AjaxFullSearch;
                            var smallSearch = AjaxSearch;
                        }
                        //Bind:
                        jQuery(\'input[name*="eq_options"]\')
                        .bind( "change", function(event, ui) {
                            smallSearch();
                        });
                        
                        jQuery("#vubla_search_form")
                        .submit(fullSearch); '.
                        $additionalBindings.'
                        
                        RunOnResultRefresh();
                }

                function RunOnResultRefresh()
                {                             
                    jQuery(".vbl-product-name-and-description").hover(
                        function () {
                            jQuery(this).find(".vbl-hover-description").css("display","block");
                        }, 
                        function () {
                            jQuery(this).find(".vbl-hover-description").css("display","none");
                        }
                    );
                    
                    if (jQuery.support.cors) { // We can make AJAX searches (not in IE8)
                        jQuery("#vbl-sort-by")
                        .add("#vbl-show-all")
                        .bind( "change", function(event, ui) {
                            AjaxSearch();
                        });
                        jQuery(".vbl-remove-with-js").hide();
                        '.$this->generateAutoCompleteBindings().'
                    }
                    else {
                        jQuery("#vbl-sort-by")
                        .add("#vbl-show-all")
                        .bind( "change", function(event, ui) {
                            AddStdVariables();
                        });
                    }
                   
                    jQuery("#vbl-toolbar").append(\'<div class="clear"></div><div id="vbl-wait"></div>\');
                     
                    jQuery(".vbl-ajax-tmp").remove();
                    hideAndRemoveSuggestions();
                }
            ';
            $generatedTemplate .= $this->generalJS();

            $generatedTemplate .=     "
            //});
            });";
            
            return $generatedTemplate;
        }

    public function generateGetJQueryAndGoOnLoad() {
        return "
        
        function getJQueryAndGoOnLoad(callback){
            var oldonload = window.onload;
            if (typeof window.onload != 'function') {
                window.onload = function() {
                    getJQueryAndGo(callback);
                };
            } else {
                window.onload = function() {
                    if (oldonload) {
                        oldonload();
                    }
                    getJQueryAndGo(callback);
                }
            }
        }
        
        function loadToHead(tag,url, success) {
    
            var script     = document.createElement(tag);
            if(tag == 'script') {
                script.src  = url;
                script.type = 'text/javascript';
            } else if (tag == 'link') {
                script.href = url;
                script.type = 'text/css';
                script.rel  = 'stylesheet';
            }
    
            var head = document.getElementsByTagName('head')[0],
            done = false;
    
            // Attach handlers for all browsers
            script.onload = script.onreadystatechange = function() {
    
                if (!done && (!this.readyState || this.readyState == 'loaded' || this.readyState == 'complete')) {
    
                    done = true;
    
                    // callback function provided as param
                    success();
    
                    script.onload = script.onreadystatechange = null;
                    //head.removeChild(script);
    
                };
    
            };
    
            head.appendChild(script);
        }
        
        function getJQueryAndGo(callback) {
            var thisPageUsingOtherJSLibrary = false;
            if(typeof \$ != 'undefined')
            {
                var tempDollar = \$;
            }
            if(typeof jQuery != 'undefined')
            {
                var tempJQuery = jQuery;
            }
            // Only do anything if jQuery isn't defined
            
            if (typeof jQuery == 'undefined' || !jQuery().delegate || jQuery.support.cors === undefined) { //last checks should ensure all the functions we need
            
                if (typeof \$ == 'function') {
                    // warning, global var
                    thisPageUsingOtherJSLibrary = true;
                }
            
                loadToHead('script','https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js', function() {
            
                    if (typeof jQuery=='undefined') {
            
                        //alert('Super failsafe - still somehow failed...')
            
                    } else {
            
                        // jQuery loaded! Make sure to use .noConflict just in case
                        //alert('fancyCode();');
            
                        if (thisPageUsingOtherJSLibrary) {
                            //alert('thisPageUsingOtherJSLibrary');
                            jQuery.noConflict();
                            callback(jQuery);
            
                                            if(typeof tempDollar != 'undefined')
                                            {
                                                \$ = tempDollar;
                                            }
                                            if(typeof tempJQuery != 'undefined')
                                            {
                                                jQuery = tempJQuery;
                                            }
            
                        } else {
            
                            //alert('Use .noConflict(), then run your jQuery Code');
                            jQuery.noConflict();
                            callback(jQuery);
                            
                        }
                    }
                });
            } 
            else 
            { // jQuery was already loaded
                //alert('jQuery was already loaded');
                //jQuery.noConflict();
                callback(jQuery);
            
            }
            
                                            if(typeof tempDollar != 'undefined')
                                            {
                                                \$ = tempDollar;
                                            }
                                            if(typeof tempJQuery != 'undefined')
                                            {
                                                jQuery = tempJQuery;
                                            }
        }
        ";
    }
    
    public function generateSuggestion() {
        $extra = '';
        if($this->enable)
        {
            $extra = '&enable=1';
        }
        return
         $this->generateDump().
    "
    function enableAutoComplete(j) {
        var jQuery = j;
        loadToHead('link', '/js/vubla/vubla.php?id=suggestion_css', function() {});".
         $this->generateVariables().
         $this->generateGetSuggestions().
         $this->generateAutoCompleteBindings().
         $this->generateHideAndRemoveSuggestions().
         $this->generateTests()." 
    }
    ".$this->generateGetJQueryAndGoOnLoad()."
    
    getJQueryAndGoOnLoad(enableAutoComplete);
    ";
    }
        private function generateVariables() {
            return "                   
                    var vubla_base          =   '".API_URL."/search/';   //Path and filename to the local vubla file (default is vubla.php)
                    var searchform          =   'vubla_search_form';   //Value of the form element's chosen attribute (product search)
                    var searchfield         =   'vbl-search-field';     //Value of the input element's chosen attribute (product search)
                    var searchform_attr     =   'id';         //Chosen attribute for controlling form (name, id, class, type etc)
                    var searchfield_attr    =   'id';         //Chosen attribute for controlling textfield (name, id, class, type etc)
                    var popup_element       =   'div';          //Element containing search suggestions (e.g. div or perhaps a list)
                    var popup_id            =   'vbl-suggestions';  //Id of the element
                    var child_element       =   'div';          //Element containing each suggestion (e.g. div, span or li)
                    var child_element_class =   'vbl-suggestion';  //Id of the element
                    var vbl_client_host     =   window.location.hostname;
                    var skip_suggestion     =   false;
                    var debug               =   false;
                    var cur_suggestion      =   0;
                    var no_suggestions      =   0;
                    var suggestion_class    =   'vbl-suggestion-no-';
                    ";
        }
        
        private function generateAutoCompleteBindings() {
            $bindingsIdents = explode(',',Settings::get('search_field_identifiers',$this->wid));
            $extraYOffset = (int)Settings::get('suggestion_y_offset',$this->wid);
            $bindingsIdents[] = '#vbl-search-field';
            $js = ' var lastElems = null;
                    var selectedSearchElem = null;
                    function SuggestDown() {
                        cur_suggestion++;
                        if(jQuery("."+suggestion_class+cur_suggestion).length == 0) {
                            cur_suggestion = 0;
                            selectedSearchElem.focus();
                        }
                        else {
                            jQuery("."+suggestion_class+cur_suggestion).focus();
                        }
                        return false;
                    }
                    
                    function SuggestUp() {
                        cur_suggestion--;
                        if( cur_suggestion < 0) {
                            cur_suggestion = no_suggestions;
                        }
                        if(cur_suggestion > 0) {
                            jQuery("."+suggestion_class+cur_suggestion).focus();
                        } else {
                            selectedSearchElem.focus();
                        } 
                    }
            ';
            foreach ($bindingsIdents as $binding) {
                if($binding) {
                    $js .= '
                    
                        var elems = jQuery("'.$binding.'");
                        if(elems.length > 0) {
                            elems.unbind("keyup");
                            elems.unbind("focus");
                            elems.unbind("click");
                            elems.bind("keyup",function(e) {
                                selectedSearchElem = jQuery("'.$binding.'");
                                switch(e.which)
                                {
                                    case 40: //Down
                                        SuggestDown();
                                        break;
                                    case 38: //Up
                                        SuggestUp();
                                        break;
                                    default: //Make new search
                                        getSuggestions(jQuery("'.$binding.'"));
                                }
                                return true;
                            });
                            elems.bind("click focus",function() {
                                selectedSearchElem = jQuery("'.$binding.'");
                                getSuggestions(jQuery("'.$binding.'"));
                                return true;
                            });
                            elems.attr("autocomplete","off");
                            lastElems = elems;
                        }';
                }
            }
            if(Settings::get('focus_on_load',$this->wid) == 1) {
                $js .= '
                    if(lastElems != null) {
                        skip_suggestion = true;
                        lastElems.focus();
                    }';
            }
            return $js .'
                    
                    //Listen for click events also on generated elements
                    jQuery("#"+popup_id+" ."+child_element_class).live("click",function() {
                        /*alert (jQuery(this) + "\n" + 
                            jQuery(this).attr("data-link") + "\n" +
                            jQuery(this).attr("class"));*/
                        location.href = jQuery(this).attr("data-link");
                    });
                    jQuery("body").undelegate("#"+popup_id+" ."+child_element_class,"keyup");
                    jQuery("body").delegate("#"+popup_id+" ."+child_element_class,"keyup",function(e) {
                                switch(e.which)
                                {
                                    case 40: //Down
                                        return SuggestDown();
                                        break;
                                    case 38: //Up
                                        return SuggestUp();
                                        break;
                                    case 13: //Enter
                                        location.href = jQuery(this).attr("data-link");
                                        break;
                                }
                                return false;
                    });
                    var arrowKeys=new Array(33,34,35,36,37,38,39,40);

                    jQuery(document).keydown(function(e) {
                          var key = e.which;
                          if(jQuery.inArray(key,arrowKeys) > -1 && jQuery("*:focus").hasClass("vbl-suggestion")) {
                              e.preventDefault();
                              return false;
                          }
                          return true;
                    });
                    
                    //Hide window when the page is clicked
                    jQuery("html").unbind("click");
                    jQuery("html").click(function() {
                        hideAndRemoveSuggestions();
                    });
                    ';
        }

        private function generateGetUrlVars() {
            return 'function getUrlVars()
                    {
                        var vars = [], hash;
                        var hashes = window.location.href.slice(window.location.href.indexOf("?") + 1).split("&");
                        for(var i = 0; i < hashes.length; i++)
                        {
                            hash = hashes[i].split("=");
                            vars.push(hash[0]);
                            vars[hash[0]] = hash[1];
                        }
                        return vars;
                    }';
        }
        
        private function generateGetUrlFileName() {
            return 'function getUrlFileName()
                    {
                        return window.location.pathname;
                    }';
        }
        
        private function generateAjaxSearch() {
            return 'function AjaxSearch() {
                        AddStdVariables();
                        AddPostVariable("#vubla_search_form","ajax_only_results","1");
                        jQuery.ajax({
                            type: "POST",
                            url: "'.API_URL.'/search/ajaxsearch.php",
                            data: jQuery("#vubla_search_form").serialize(),
                            success: function(data) {
                                jQuery("#vbl-content-wrapper").html(data);
                                actionurl = jQuery("#vubla_search_form").attr("action");
                                var href = jQuery("#vbl-footer-pages > a").attr("href");
                                if (href != null)
                                {
                                    params = href.split("?")[1];
                                    jQuery("#vbl-footer-pages > a.vbl-page-link").attr("href",actionurl+"?"+params);
                                }
                                RunOnResultRefresh();
                            }
                        });
                        jQuery(".vbl-hide-on-load").hide();
                        jQuery("#vbl-wait").show();
                        
                        jQuery(".vbl-ajax-tmp").remove();
                    }';
        }

        private function generateAjaxFullSearch() {
            $resetWidgetsOnSearch = (int)Settings::get('reset_widgets_on_search',$this->wid);
            $resetToolbarOnSearch = (int)Settings::get('reset_toolbar_on_search',$this->wid);
            return 'function AjaxFullSearch () {
                            AddStdVariables();
                            AddPostVariable("#vubla_search_form","ajax_full_search","1");
                            '.($resetWidgetsOnSearch == 1 ? 'ResetWidgets();' : '').'
                            '.($resetToolbarOnSearch == 1 ? 'ResetToolbar();' : '').'
                            jQuery.ajax({
                                type: "POST",
                                url: "'.API_URL.'/search/ajaxsearch.php",
                                data: jQuery("#vubla_search_form").serialize(),
                                success: function(data) {
                                    jQuery("#vubla").html(data);
                                    RunOnRefresh();
                                }
                            });
                            jQuery(".vbl-hide-on-load").hide();
                            jQuery("#vbl-wait").show();
                            jQuery(".vbl-ajax-tmp").remove();
                            return false;
                    }';
        }

        private function generateResetWidgets() {
            return 'function ResetWidgets() {
                        jQuery(\'input[name="eq_options[category_id][]"]\')
                        .attr( "checked", false);
                        
                        jQuery("#vbl-price-from").attr("disabled","disabled");
                        jQuery("#vbl-price-to").attr("disabled","disabled");
                        jQuery("#vbl-slider-active").val("no");
                    }';
        }

        private function generateResetToolbar() {
            return 'function ResetToolbar() {
                        jQuery("#vbl-sort-by>:selected")
                        .attr("selected",false);
                        
                        jQuery("#vbl-show-all")
                        .attr( "checked", false);
                    }';
        }

        private function generateAddStdVariables() {
            return 'function AddStdVariables() {
                        q = jQuery("#vbl-search-field").val();
                        AddPostVariable("#vubla_search_form","host",vbl_client_host);
                        AddPostVariable("#vubla_search_form","q",q);
                        //AddPostVariable("#vubla_search_form","keywords",q);
                        params = getUrlVars();
                        if(params["enable"] != null)
                        {
                            AddPostVariable("#vubla_search_form","enable",params["enable"]);
                        }
                        if(params["vubla_url"] != null)
                        {
                            AddPostVariable("#vubla_search_form","vubla_url",decodeURIComponent(params["vubla_url"]));
                        }
                        if(params["return_host"] != null)
                        {
                            AddPostVariable("#vubla_search_form","return_host",decodeURIComponent(params["return_host"]));
                        }
                        if(params["file"] != null)
                        {
                            AddPostVariable("#vubla_search_form","file",decodeURIComponent(params["file"]));
                        }
                        else 
                        {
                            AddPostVariable("#vubla_search_form","file",getUrlFileName());
                        }
                        if(params["store_id"] != null)
                        {
                            AddPostVariable("#vubla_search_form","store_id",params["store_id"]);
                        }
                        if(params["ip"] != null)
                        {
                            AddPostVariable("#vubla_search_form","ip",decodeURIComponent(params["ip"]));
                        }
                        if(params["useragent"] != null)
                        {
                            AddPostVariable("#vubla_search_form","useragent",decodeURIComponent(params["useragent"]));
                        }
                    }';
        }

        private function generateAddPostVariable() {
            return 'function AddPostVariable(formSelector,name,value) {
                         jQuery(formSelector).append("<input class=\\"vbl-ajax-tmp\\" type=\\"hidden\\" name=\\""+name+"\\" value=\\""+value+"\\"/>");
                    }';
        }

        private function generateHideAndRemoveSuggestions() {
            return "function hideAndRemoveSuggestions() {
                        //jQuery('#'+popup_id).slideUp(300,function() {
                            jQuery('#'+popup_id).remove();
                        //});
                        no_suggestions = 0;
                        cur_suggestion = 0;
                    }";
        }

        private function generateGetSuggestions() {
            $doGenerate = (int)Settings::get('autocomplete',$this->wid);
            if($doGenerate == 1) {
                return "
                    function getSuggestions(elementToPlaceBelow,callback) {
                        function call_the_callback() 
                        {
                            if(typeof callback == 'function')
                            {
                                callback();
                            }
                        }
                        
                        if(skip_suggestion)
                        {
                            skip_suggestion = false;
                            return false;
                        }
                        
                        var q = jQuery.trim(elementToPlaceBelow.val()); //Textfield value
                        //Only send the request when a significant amount of letters were typed
                        if(q.length >= 3) {
                            jQuery.ajax({
                                type: 'POST', 
                                dataType: 'json',
                                url: '".API_URL."/search/ajaxsearch.php',
                                data: '".($this->enable == 1 ?
                                    "enable=1&":
                                    ""
                                )."q='+q+'&suggestions=1&host='+vbl_client_host,
                                success: function(data) {
                                    if(data != '' && data != null && data.results != ''
                                     && data.q == jQuery.trim(elementToPlaceBelow.val())
                                    ) {
                                        var html = '';
                                        var tab_index = 1;
             
                                       jQuery.each(data.results, function(i,item) {
                                            html += '<'+child_element+' data-link=\"'+item.link+'\" class=\"'+child_element_class+' '+suggestion_class+(i+1)+'\" tabindex=\"'+(i+tab_index)+'\">';
                                            html += '   <div class=\"vbl-suggestion-image-surrounder\">';
                                            html += '       <div class=\"vbl-suggestion-centerring-wrapper\">';
                                            html += '           <img class=\"vbl-suggestion-image\" style=\"vertical-align:top;\" src=\"'+item.image_link+'\" alt=\"\" />';
                                            html += '       </div>';
                                            html += '   </div>';
                                            html += '   <div class=\"vbl-suggestion-text\">';
                                            html += '       <div class=\"vbl-suggestion-title\">'+item.name+'</div>';
                                            html += '       <div class=\"vbl-suggestion-description\">';
                                            //html +=             item.description.replace(/(<([^>]+)>)/ig,'').substring(0,60);
                                            //if(item.description.replace(/(<([^>]+)>)/ig,'').length > 60) {
                                            //    html += '...';
                                            //}
                                            html += '       </div>';
                                            html += '   </div>';
                                            html += '</'+child_element+'>';
                                            no_suggestions = i+1;
                                        });
                                        
                                        if(elementToPlaceBelow.parent().children(popup_element + '#'+popup_id).size() == 0)
                                        {
                                            var offset = elementToPlaceBelow.position();
                                            yOffset = 0;
                                            if(elementToPlaceBelow.css('position') == 'static') {
                                                yOffset += elementToPlaceBelow.outerHeight();
                                            }
                                            
                                            hideAndRemoveSuggestions();
                                            elementToPlaceBelow.after('<'+popup_element+' id=\"'+popup_id+'\" style=\"left: '+(offset.left)+'px; top: '+(offset.top + yOffset)+'px;\"></'+popup_element+'>');
                                            jQuery('#'+popup_id).slideDown(300);
                                            
                                        }
                                        cur_suggestion = 0;
                                        jQuery('#'+popup_id).html(html);
                                    }
                                    else
                                    {
                                        hideAndRemoveSuggestions();
                                    }
                                    call_the_callback();
                                }
                                });
                            
                            return true;
                        } else {
                            hideAndRemoveSuggestions();
                            return false;
                        }
                    }";
            }
            else {
                return "function getSuggestions(elementToPlaceBelow,callback) {}";
            }
        }
        
        private function generalJS() {
            $js= $this->generateHideAndRemoveSuggestions().
                 $this->generateGetSuggestions().
                 $this->generateGetUrlVars().
                 $this->generateGetUrlFileName().
                 $this->generateAjaxSearch().
                 $this->generateAjaxFullSearch().
                 $this->generateResetWidgets().
                 $this->generateResetToolbar().
                 $this->generateAddPostVariable().
                 $this->generateAddStdVariables().
                 'RunOnRefresh();';
             return $js;
        }
        
        private function generateJQueryUI()
        {
            return file_get_contents(CLASS_FOLDER.'/js/jquery-ui.js');
        }
        
        private function generateJQuery()
        {
            return file_get_contents(CLASS_FOLDER.'/js/jquery.js');
        }
        
        private function generateDump() {
            return '
            function dump(obj) {
                var out = "";
                for (var i in obj) {
                    out += i + ": " + obj[i] + "\n";
                }
            
                alert(out);
            
                // or, if you wanted to avoid alerts...
            
                var pre = document.createElement("pre");
                pre.innerHTML = out;
                document.body.appendChild(pre)
            }';
        }

        private function generateTests() {
            if($this->test) 
            {
                $testCode = file_get_contents($this->test);
                return $testCode;
            }
            else
            {
                return '';
            }
        }
    }
        
