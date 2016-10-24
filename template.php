<?php

    class Template extends BaseTemplateObject{
        // use GetHandler;
        /*
        *Retrieve templates for search results
        */
        static function getTemplates($type = 'searchlayout') {
            $db = VPDO::getVdo(DB_METADATA);
            $sql = 'SELECT * FROM search_templates WHERE type = ' . $db->quote($type);
            return $db->getTableList($sql);
        }
        
        /*
        *Retrieve info about one template
        */
        static function getTemplate($id) {
            $db = VPDO::getVdo(DB_METADATA);
            $sql = 'SELECT * FROM search_templates WHERE id = ' . $db->quote($id);
            return $db->getTableList($sql);         
        }
        
        /*
        *Retrieve info about chosen template
        */
        static function getCurrentTemplate($wid) {
            $db = VPDO::getVdo(DB_PREFIX.$wid);
            
            $sql = 'SELECT * FROM search_templates WHERE active = 1';
            return $db->getTableList($sql);
        }
        
        /*
        *Check if the id belongs to the active template
        */
        static function isCurrentTemplate($id,$wid) {
            $db = VPDO::getVdo(DB_PREFIX.$wid);
            $sql = 'SELECT active FROM search_templates WHERE template_id = ' . $db->quote($id) . ' AND active = 1';

            if($db->fetchOne($sql)) {
                return true;    
            }
            else { return false; }
        }
        
        /*
        *Set current templates
        */
        static function setCurrentTemplate($id,$wid) {
            $db = VPDO::getVdo(DB_PREFIX.$wid);

            $sql = 'SELECT * FROM search_templates WHERE template_id = ' . $db->quote($id);
            
            if($db->fetchOne($sql)) {
                $sql = 'UPDATE search_templates SET active = 0; UPDATE search_templates SET active = 1 WHERE template_id = ' . $db->quote($id);
                $db->exec($sql);
            }
            else {
                $meta = VPDO::getVdo(DB_METADATA);
                $attributes = $meta->fetchOne('SELECT standard_attributes FROM search_templates WHERE id = ' . $db->quote($id));
                
                $sql = 'UPDATE search_templates SET active = 0; INSERT INTO search_templates(template_id,attributes,active) VALUES('. $db->quote($id) . ',' . $db->quote($attributes) . ',1)';
                $db->exec($sql); 
            }
        }
        
        /*
        *Check if ever a template was chosen
        */
        static function aTemplateWasEverChosen($wid) {
            $db = VPDO::getVdo(DB_PREFIX.$wid);
            
            $sql = 'SELECT * FROM search_templates';
            
            if(!$db->fetchOne($sql)) {
                return false;
            }
            else {
                return true;    
            }
        }
        
        static function getAttributes($id) {
            //ie($id);
            $meta = VPDO::getVdo(DB_METADATA);
            $attributes = $meta->fetchOne('SELECT attributes FROM search_templates WHERE id = ' . $meta->quote($id));            
            return json_decode($attributes);
        }
        
        static function getCurrentAttributes($wid) {
            $db = VPDO::getVdo(DB_PREFIX.$wid);
            $attributes = $db->fetchOne('SELECT attributes FROM search_templates WHERE active = 1');
            return json_decode($attributes);
        }
        
        static function setAttributes($wid,$attr) {
            $db = VPDO::getVdo(DB_PREFIX.$wid);
            $attributes = json_encode($attr);
            $sql = 'UPDATE search_templates SET attributes = ' . $db->quote($attributes) . ' WHERE active = 1';
            $db->exec($sql);    
        }
        
      
        private $search;
        private $attributes = array();
        private $onlyResult = false;
        
        public $searchResult;
        
        function __construct($wid, $host, SearchResult $search){
            
            $this->host = $host;
            parent::__construct($wid,$search);
        }
        
        public function generateCSS($onlyResult) {
            $template = new CSSGenerator($this->wid);
            return '<style type="text/css">
            ' . $template->generate($onlyResult) . '
            </style>';
        }
        
        /*
        *Builds the actual template from blueprint php-file and database data
        */
        static function generateCurrentTemplate($wid, $host, SearchResult $search) {
          
            $template = new Template($wid, $host,$search);
            return $template->generateHtml();         
        }
        
        static function generateCurrentTemplateResultsForAJAX($wid, $host, SearchResult $search) {
          
            $template = new Template($wid, $host,$search);
            return $template->generateAJAXHtml();         
        }
        
        static function generateCurrentVisualTemplateResults($wid, $host, SearchResult $search) {
          
            $template = new Template($wid, $host,$search);
            return $template->generateFullHtmlForAJAX();         
        }
        
        function generateAJAXHtml() {
            $onlyResult = $this->onlyResult;
            $search = $this->searchResult;
            $host = $this->host;
            $wid = $this->wid;           
          
            $db = VPDO::getVdo(DB_PREFIX.$wid);
            $sql = 'SELECT template_id,attributes FROM search_templates WHERE active = 1';
            $active_template = $db->getTableList($sql);
            $active_template = $active_template[0];

            $this->attributes = json_decode($active_template->attributes,TRUE);
            $structure = $this->generateResults($active_template,$search->products);
            
            
            //###############GET SORT/FILTER AREAS################//

            $toolbar = self::generateToolbar($this->searchResult);
            $footer = self::generateFooter($this->searchResult);

            //##################PUT IT TOGETHER###################//
            $generatedTemplate = $toolbar.$structure.$footer;

            //##################GENERATE SOME JS MAYBE################//
        
            return $generatedTemplate;
        }
        
        function generateHtml(){
            $onlyResult = $this->onlyResult;
            $search = $this->searchResult;
            $host = $this->host;
            $wid = $this->wid;           
          
            $db = VPDO::getVdo(DB_PREFIX.$wid);
            $sql = 'SELECT template_id,attributes FROM search_templates WHERE active = 1';
            $active_template = $db->getTableList($sql);
            
            $active_template = $active_template[0];
            if(!is_object($active_template))
            {
                throw new VublaException("No template has been set!");
            }
            $this->attributes = json_decode($active_template->attributes,TRUE);
            
            
            //##########GENERATE BASE (#VUBLA BOX) AND TEMPLATE FROM BLUEPRINT##########//
            //START VUBLA HEAD
            $generatedTemplate = $this->generateCSS($onlyResult);             
            
            $generatedTemplate .= "\n<div id=\"vubla\">\n";
            $generatedTemplate .= $this->generateVisualHtml($active_template,$onlyResult);
            $generatedTemplate .= "</div>\n";

            //##################GENERATE SOME JS MAYBE################//

            $generatedTemplate .= "
            <!--<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js\" type=\"text/javascript\"></script>-->
            <!--<script src=\"https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.min.js\" type=\"text/javascript\"></script>-->
            ";   
            
            $generatedTemplate .= '<script type="text/javascript">'.$this->generateAllJS().'</script>';
			if(Settings::get('split_test',$this->wid)) 
			{
				$setAccount = Settings::get('gwo._setAccount',$this->wid);
				$trackPageview = Settings::get('gwo._trackPageview',$this->wid);
                $k = Settings::get('gwo.k',$this->wid);
				//$generatedTemplate;
			}
        
            return $generatedTemplate;
        }

        function generateFullHtmlForAJAX()
        {
            $onlyResult = $this->onlyResult;
            $search = $this->searchResult;
            $host = $this->host;
            $wid = $this->wid;           
          
            $db = VPDO::getVdo(DB_PREFIX.$wid);
            $sql = 'SELECT template_id,attributes FROM search_templates WHERE active = 1';
            $active_template = $db->getTableList($sql);
            $active_template = $active_template[0];

            $this->attributes = json_decode($active_template->attributes,TRUE);

            return $this->generateVisualHtml($active_template,$onlyResult);;
        }
        
        function generateVisualHtml($active_template,$onlyResult = FALSE)
        {
            $structure = $this->generateResults($active_template,$this->searchResult->products);
            $generatedTemplate = "<form id=\"vubla_search_form\" action=\"".$this->getShopFormAction()."\" method=\"get\">\n";
            // There should also be a input field in the search layout which displays the key words(Going to be in the top bar) 
            // We need a form. We do it without JS(initially)
            // Price slider should be two text fields if JS is disabled. 

            //END VUBLA HEAD
         
                   
            
            //###############GET SORT/FILTER AREAS################//
            if($onlyResult){
                $toolbar = '';
                $sidebar = '';
                $footer ='';
            } else {
                $toolbar = self::generateToolbar($this->searchResult);
                $sidebar = self::generateSidebar($this->searchResult->widgets);
                $footer = self::generateFooter($this->searchResult);
            }
            //##################PUT IT TOGETHER###################//
            $generatedTemplate .= $sidebar."\n<div id=\"vbl-content-wrapper\">\n".$toolbar.$structure.$footer."\n</div>\n";
            $generatedTemplate .= "<input type=\"hidden\" name=\"param\" value=\"$this->param\">";
            $generatedTemplate .= "<input type=\"hidden\" name=\"vat_disp\" value=\"$this->vubla_enable_vat\">";
			$generatedTemplate .= "<input type=\"hidden\" name=\"ip\" value=\"$this->ip\">";
			$generatedTemplate .= "<input type=\"hidden\" name=\"useragent\" value=\"$this->useragent\">";
            //$generatedTemplate .= "<input type=\"hidden\" name=\"store_id\" value=\"$this->store_id\">";
            
            $generatedTemplate .= "</form>\n";
            
            return $generatedTemplate;
            
        }

        function generateAllJS() {
            $jsGen = new JavaScriptGenerator($this->wid);
            
            return $jsGen->generate($this->searchResult->widgets);
        }

        
        
        public function generateResults($active_template,$products) {
            
            $wid = $this->wid;
            $result_row_height = Settings::get('result_row_height',$wid);
			
			$pictureHeight = (int)Settings::get('picture_height',$this->wid); //must be a number!
			if(is_null($pictureHeight) || $pictureHeight <= 0) {
				$pictureHeight = 100; //must be a number!
			}
			$currency = Settings::get('default_currency',$this->wid);
			if(is_null($currency)) {
				$currency = 'kr.';
			}
            
            if($products == null) {
                $products = array();
            }
            
           

            /// This part where it generated messages has been moved to its own function.
            /// The template_idXX.php does not write that anymore. 
     
            //##########MODIFY DATA##########//
            
            
            $extra_product_title_style = settings::get('extra_product_title_style',$wid);
            $emptyTemplateText = Settings::get('empty_template_text',$wid );
            
            //START TEMPLATE
            include('templates/searchlayout/template_id' . $active_template->template_id . '.php');
            //END TEMPLATE   
            return $structure;
        }
        
        /*
        *Builds the actual template from blueprint php-file with dummy data
        */
        static function generatePreview($wid) {
            $res = new SearchResult();
            $host = "preview";
            $preview = new Template($wid, $host, $res);
            return $preview->generatePreviewHTML();
        }
        
        function generatePreviewHTML() {
            $wid = $this->wid;
            $db = VPDO::getVdo(DB_PREFIX.$this->wid);
            $sql = 'SELECT template_id,attributes FROM search_templates WHERE active = 1';
            $active_template = $db->getTableList($sql);
            $active_template = $active_template[0];
            
            $userdefinedkeywords = array(
                                            (object)array('url' => '#', 'text' => 'nøgleord1'),
                                            (object)array('url' => '#', 'text' => 'nøgleord2')
                                          );
            
            $products = array();
            $alpha = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','W','X','Y','Z','Æ','Ø','Å');

            if($active_template->template_id == 1) {
                $amount = 11;
            }
            elseif($active_template->template_id == 2) {
                $amount = 12;
            }
            else {
                $amount = 15;
            }           
            
            for($i = 0; $i < $amount; $i++) {
                $price = rand(1,10000);
                $percent = '0.'.rand(0,30);
                $dprice = $price-((float)$percent*$price);
                $products[] = (object)array('name' => 'Produkt '. $alpha[$i], 'link' => '#', 'image_link' => API_URL . '/images/no-picture.png', 'buy_link' => '#', 'price' => $price, 'discount_price' => $dprice, 'description' => 'Lorem Ipsum');    
            }       
            /*$products = array(
                            (object)array('name' => 'Produkt A', 'link' => '#', 'image_link' => API_URL . '/images/no-picture.png', 'buy_link' => '#', 'price' => 30.00, 'discount_price' => 0, 'description' => 'Lorem Ipsum'),
                            (object)array('name' => 'Produkt B', 'link' => '#', 'image_link' => API_URL . '/images/no-picture.png', 'buy_link' => '#', 'price' => 30.00, 'discount_price' => 0, 'description' => 'Lorem Ipsum'),
                            (object)array('name' => 'Produkt C', 'link' => '#', 'image_link' => API_URL . '/images/no-picture.png', 'buy_link' => '#', 'price' => 30.00, 'discount_price' => 25.00, 'description' => 'Lorem Ipsum'),
                            (object)array('name' => 'Produkt D', 'link' => '#', 'image_link' => API_URL . '/images/no-picture.png', 'buy_link' => '#', 'price' => 30.00, 'discount_price' => 0, 'description' => 'Lorem Ipsum'),
                            (object)array('name' => 'Produkt E', 'link' => '#', 'image_link' => API_URL . '/images/no-picture.png', 'buy_link' => '#', 'price' => 30.00, 'discount_price' => 0, 'description' => 'Lorem Ipsum'),
                            (object)array('name' => 'Produkt F', 'link' => '#', 'image_link' => API_URL . '/images/no-picture.png', 'buy_link' => '#', 'price' => 30.00, 'discount_price' => 25.00, 'description' => 'Lorem Ipsum'),
                            (object)array('name' => 'Produkt G', 'link' => '#', 'image_link' => API_URL . '/images/no-picture.png', 'buy_link' => '#', 'price' => 30.00, 'discount_price' => 0, 'description' => 'Lorem Ipsum'),
                            (object)array('name' => 'Produkt H', 'link' => '#', 'image_link' => API_URL . '/images/no-picture.png', 'buy_link' => '#', 'price' => 30.00, 'discount_price' => 0, 'description' => 'Lorem Ipsum'),
                            (object)array('name' => 'Produkt I', 'link' => '#', 'image_link' => API_URL . '/images/no-picture.png', 'buy_link' => '#', 'price' => 30.00, 'discount_price' => 25.00, 'description' => 'Lorem Ipsum'),
                            (object)array('name' => 'Produkt J', 'link' => '#', 'image_link' => API_URL . '/images/no-picture.png', 'buy_link' => '#', 'price' => 30.00, 'discount_price' => 0, 'description' => 'Lorem Ipsum'),
                            (object)array('name' => 'Produkt K', 'link' => '#', 'image_link' => API_URL . '/images/no-picture.png', 'buy_link' => '#', 'price' => 30.00, 'discount_price' => 0, 'description' => 'Lorem Ipsum'),
                            (object)array('name' => 'Produkt L', 'link' => '#', 'image_link' => API_URL . '/images/no-picture.png', 'buy_link' => '#', 'price' => 30.00, 'discount_price' => 25.00, 'description' => 'Lorem Ipsum'),
                            (object)array('name' => 'Produkt M', 'link' => '#', 'image_link' => API_URL . '/images/no-picture.png', 'buy_link' => '#', 'price' => 30.00, 'discount_price' => 0, 'description' => 'Lorem Ipsum'),
                            (object)array('name' => 'Produkt N', 'link' => '#', 'image_link' => API_URL . '/images/no-picture.png', 'buy_link' => '#', 'price' => 30.00, 'discount_price' => 0, 'description' => 'Lorem Ipsum'),
                            (object)array('name' => 'Produkt O', 'link' => '#', 'image_link' => API_URL . '/images/no-picture.png', 'buy_link' => '#', 'price' => 30.00, 'discount_price' => 25.00, 'description' => 'Lorem Ipsum'),
                            (object)array('name' => 'Produkt P', 'link' => '#', 'image_link' => API_URL . '/images/no-picture.png', 'buy_link' => '#', 'price' => 30.00, 'discount_price' => 0, 'description' => 'Lorem Ipsum'),
                            (object)array('name' => 'Produkt Q', 'link' => '#', 'image_link' => API_URL . '/images/no-picture.png', 'buy_link' => '#', 'price' => 30.00, 'discount_price' => 0, 'description' => 'Lorem Ipsum'),
                            (object)array('name' => 'Produkt R', 'link' => '#', 'image_link' => API_URL . '/images/no-picture.png', 'buy_link' => '#', 'price' => 30.00, 'discount_price' => 25.00, 'description' => 'Lorem Ipsum')
                            );// /* */
                            
            $this->attributes = json_decode($active_template->attributes,TRUE);
        
            //##########MESSAGES##########//
            if(sizeof($products) < 2 && sizeof($products) > 0) {
                $the_word_products = "produkt";
            }
            else {
                $the_word_products = "produkter";
            }

            $msg = 'Vi fandt <b>'.sizeof($products).'</b> '.$the_word_products.' for <b>"testord"</b>';
            
            //##########MODIFY DATA##########//
            /*
            foreach($products as $product) {
                
                //START MODIFY PRICE
                $vat_multiplyer = (float)Settings::get('vat_multiplyer', $this->wid);
                $price_w_vat = (float) floatval($product->price) * $vat_multiplyer;
                $dprice_w_vat = (float) floatval($product->discount_price) * $vat_multiplyer;
                if($price_w_vat != 0) {
                    $dprice_percent = round(100-(($dprice_w_vat/$price_w_vat)*100));
                }
                else    {
                    $dprice_percent = 0;
                }

                $product->price = number_format( (float) floatval($product->price) * $vat_multiplyer, 2,',' ,'.');
                $product->discount_price = number_format( (float) floatval($product->discount_price) * $vat_multiplyer, 2,',' ,'.');
                //END MODIFY PRICE
                
            }
            */
                
            //##########GENERATE TEMPLATE FROM BLUEPRINT##########//
            //START VUBLA HEAD
            $generatedTemplate = "
            \n<style type=\"text/css\">\n
                #vubla {

                }
                
                #vubla a:link {text-decoration: none; font-weight: bold; color: " . $this->attributes['Link Color'] . ";}
                #vubla a:visited {text-decoration: none; font-weight: bold; color: " . $this->attributes['Visited Link Color'] . ";}
                #vubla a:hover {text-decoration: none; font-weight: bold; color: " . $this->attributes['Focused Link Color'] . ";}
                #vubla a:active {text-decoration: none; font-weight: bold; color: " . $this->attributes['Focused Link Color'] . ";}             
            \n</style>\n";
            $generatedTemplate .= "\n<div id=\"vubla\">\n";
            include('templates/searchlayout/template_id' . $active_template->template_id . '.php');
            $generatedTemplate .= $structure;
            $generatedTemplate .= "\n</div>\n";
            return $generatedTemplate;
        } 
        
        /*
        *Generates a toolbar preferably on top of the SERP
        */
        private function generateToolbar(SearchResult $search) {

            $tt = new TemplateToolbar($this->wid,$search);
            return $tt->generateHtml();
        }
        
        /*
        *Generates a sidebar (widget area) preferably on the left side of the SERP
        */
        private function generateSidebar($widgets) {

            $sidebar = "<div id=\"vbl-sidebar\">\n";

            foreach($widgets as $widget) {
                $sidebar .= $widget->generateHtml();
            }
           
            $sidebar .= "\n</div>\n";
            
            
            return $sidebar;
        }
        
        /*
        *Generates a footer because Rasmus said so ;)
        */
        private function generateFooter(SearchResult $sr) {
            /*
            $footer = "<div id=\"vbl-footer\">\n";
            $footer .= "<div id=\"vbl-footer-products\">\n";
            $footer .= "Viser <b>".$this->offset*Settings::get('max_search_results')."-". ceil(Settings::get('max_search_results')*(1+$this->offset))."</b> af <b>".$sr->number_of_products."</b> produkter";
            $footer .= "\n</div>\n";
            $footer .= "<div id=\"vbl-footer-pages\">\n";
            $footer .= "<b>1</b> <a href=\"#\">2</a> <a href=\"#\">3</a> av <a href=\"#\">11</a>";
            $footer .= "\n</div>\n";
            $footer .= "\n</div>\n";
             * *
             */
            $footer = new TemplateFooter($this->wid,$sr);
            
            
            return $footer->generateHtml(); 
        }
        
        
      
    }
