<?php

$columns = 3;
if(isset($this->attributes['Kolonne Antal'])) {
    $columns = $this->attributes['Kolonne Antal'];
}
if(!isset($result_row_height)) {
	$result_row_height = 300;
}
$structure = "<style type=\"text/css\">
	#vbl-serp {
		font-family: " . $this->attributes['Font Stack'] . ";
		color: " . $this->attributes['Text Color'] . ";
	}

	#vbl-product-list {
		min-height: ".$result_row_height."px;

		margin: 0;

		padding: 0;

		font-size: " . (11*($this->attributes['Font Size']/100)+11) . "px;

		list-style: none;
		";
                        
    if(!Settings::get('frame_on_product_hover',$this->wid)){
    
        $structure .= '
        border-bottom-width: 1px;
		border-bottom-style: solid;
		border-bottom-color: ' . $this->attributes['Secondary Color'] . ';';
    }
    $product_list_width = ((int)(100/$columns));
    if($columns == 3){
       $product_list_width ;
    }
    $structure .= "
	}

	#vbl-product-list li {
		height: ".($result_row_height)."px;
		width: ".((int)(100/$columns)-(0.3))."%;
		
		display: block;
		
		position: relative;
		margin: 0;
		float: left;";
                        
    if(Settings::get('frame_on_product_hover',$this->wid)){
    
        $structure .= '
        border: 1px solid white;';
    }
  $structure .= "
	}
	";
                        
    if(Settings::get('frame_on_product_hover',$this->wid)){
    
        $structure .= '
    #vbl-product-list li:hover {
        border-color:  '. $this->attributes['Secondary Color'] .'
    }';
    }
  $structure .= "
	
	#vbl-product-list .vbl-product {
	    height: ".($result_row_height-10)."px;
	    overflow: hidden;
	    margin: 10px 10px 0px 10px;
	    ".Settings::get('extra_product_style',$wid)."
	}
	
	.grid-stroke {
		border-right: 1px solid " . $this->attributes['Secondary Color'] . ";
	}
	
	.vbl-product-img {
		max-height: ".$pictureHeight."px;
		width: auto;
		max-width: 100%;
		
		margin: auto;
	}
	
	.vbl-centerring-wrapper {
		
		display: table-cell;
		vertical-align: middle;
	}
	
	.vbl-image-surrounder{
		height: ".$pictureHeight."px;
		width: auto;
		max-width: 100%;
		display: table;
		
		margin: 0 auto;
		margin-bottom: 20px;
		padding: 0;
	}
    
    .vbl-inside {
        border: 1px solid " . $this->attributes['Secondary Color'] . ";
    }
    
    .vbl-product-name-and-description
    {
        overflow: hidden;";
        if($this->host == 'safegear.dk' || $this->host == 'www.safegear.dk') {
            $structure .= "height: ".($result_row_height-90-60)."px";
        } elseif($this->host == 'avformshop.dk'  || $this->host == 'www.avformshop.dk') {
           $structure .= "height: ".($result_row_height-90-30)."px";
        } else { 
            $structure .= "height: ".($result_row_height-90)."px";
        }
        $structure .= "
    }
    
	.vbl-product-name {
		margin: 0;

		padding: 0;

		font-size: " . (15*($this->attributes['Font Size']/100)+15) . "px;
		font-weight: bold;
	}

	.vbl-product-cost {
		margin: 5px 0px 10px 0px;
		padding: 0;
        bottom: 40px;
		font-weight: bold;
	}

	.vbl-product-discounted {
		margin: 0;

		padding: 0;
        font-size: " . (12*($this->attributes['Font Size']/100)+12) . "px;
		text-decoration: line-through;
	    ".Settings::get('extra_dicounted_price_style',$wid)."
	}
	
	.vbl-hover-description {
		background: none repeat scroll 0 0 white;
		color: black;
		cursor: pointer;
		display: none;
		left: 0;
		min-width: 80%;
		height: ".($pictureHeight-10)."px;
		opacity: 0.8;
		padding: 20px 10px;
		position: absolute;
		z-index: 0;
		font-weight: normal;
		top: 0;
	}
	
	.vbl-hover-description:hover {
		display: block;
	}

	.vbl-product-discount {
		margin: 0;

		padding: 0;
        font-size: " . (14*($this->attributes['Font Size']/100)+14) . "px;
		font-weight: bold;
	    ".Settings::get('extra_price_style',$wid)."
	}
	
	.vbl-product-description {			
		margin: 10px 0px 0px 0px;
        overflow-y: hidden;
		padding: 0;
	}";	
		
	$structure .= ".vbl-buttons {
		height: 30px;";
		if($this->attributes['More Info Button'] == 'none') {
			//$structure .= "text-align: center;";
		}
	$structure .= "
		width: auto;
		max-width: 100%;
        bottom: 10px;
		display: block;
		margin: 0 auto;
	}

	.vbl-buy-now-button {
		";
		if($this->attributes['More Info Button'] == 'none') {
			$structure .= "margin: 0 auto;";
		}
		else 
		{
			$structure .= "margin-left: 5px;";
		}
		

if(false === strpos($this->attributes['Buy Now Button'],'http://')) {
	$structure .= "
		height: 27px;
		width: 71px;	
		display: inline-block;	
	
		text-indent: -9999px;
		
		background: url('http://api.vubla.com/images/btn_buy_" . $this->attributes['Buy Now Button'] . ".png');
	}
	
	.vbl-buy-now-button:hover, .vbl-buy-now-button:active {
		background-position: -71px 0px;\n";
}

	$structure .= "}\n\n";
	
	$structure .= ".vbl-more-info-button {\n\n";
		
if(false === strpos($this->attributes['More Info Button'],'http://')) {
	$structure .= "
		height: 27px;
		width: 71px;
		
		display: inline-block;

		text-indent: -9999px;
		
		background: url('http://api.vubla.com/images/btn_info_" . $this->attributes['More Info Button'] . ".png');		
	}
	
	.vbl-more-info-button:hover, .vbl-more-info-button:active {
		background-position: -71px 0px;\n";
}
$structure .= "}\n\n";
	
$structure .= '.clear { clear: both; }
</style>
<div id="vbl-serp">';

if(empty($products))
{
    $structure .= 
    "<div id=\"vbl-no-result\">
        <div id=\"vbl-no-result-headline\">Fandt du ikke varen du søgte?</div>
    
        <div id=\"vbl-no-result-box\">
            <div id=\"vbl-no-result-text\">
                " . $emptyTemplateText .  "
            </div>
            <img id=\"vbl-photo\"  src=\"http://api.vubla.com/images/photo.jpg\" alt=\"Photo\" />
        </div>
    </div>";
}
else
{
	$i = 0;
	
	foreach($products as $product) {
		
	if($i % $columns == 0 && $i != 0) {
		$structure .= "<div class=\"clear\"></div></ul>";
	}
	if($i % $columns == 0) {
	$structure .= "<ul id=\"vbl-product-list\">";
	}
	$structure .= "<li data-price=\"";
	
	if(!is_null($product->discount_price) && $product->discount_price != 0) {
		 $structure .= $product->discount_price . "\"";
	}
	else {
		 $structure .= $product->price . "\"";
	}
	if($i % $columns != $columns-1) {
	$structure .= " class=\"grid-stroke\"";	
	}
	
	$structure .= "><div class=\"vbl-product\"><div class=\"vbl-product-name-and-description\">
			<a class=\"vbl-product-name\" style=\"".$extra_product_title_style."\" href=\"$product->link\">
				<div class=\"vbl-image-surrounder\">
					<div class=\"vbl-centerring-wrapper \">
						<img class=\"vbl-product-img\" src=\"$product->image_link\" alt=\"".str_replace('–','-', $product->name)."\" />
					</div>
				</div>
			<!--<div class=\"img\"></div>-->
			".str_replace('–','-',$product->name)."</a><br />";
          
            
       ########## SKUS 
    $show_sku = settings::get('show_sku', $wid);
    if($show_sku && !is_null($product->sku)){
        $structure .= '<div class="vbl-product-sku">';
        
        if($show_sku == 1){
            $structure .= $product->sku;
        } else { 
            $structure .= str_replace('[@]',$product->sku, $show_sku );            
        }
        $structure .= '</div>';
        
    }
	
	if(Settings::get('show_description_on_hover',$wid)) {
		
		$structure .= "	<a href=\"$product->link\"><div class=\"vbl-hover-description\">
				$product->description
				
			</div></a>";
	} else {
		$structure .= "	<div class=\"vbl-product-description\">
				$product->description
			</div>";
	}
	$structure .="
			</div>
			<div class=\"vbl-product-cost\">";
			
	########### SHOW PRODUCTS STOCK ########## 
    $show_product_in_stock = settings::get('show_product_in_stock',$wid);
    if($show_product_in_stock && isset($product->quantity) && strpos($show_product_in_stock,'|') !== false){
       list($in_stock, $not_in_stock) = explode('|', $show_product_in_stock );
        /// The setting should contain a pipe which is used as a separator
       $structure .= '<div class="vbl-moms">Lagerstatus: ';
       if($product->quantity){
           $structure .= $in_stock;
       } else {
           $structure .= $not_in_stock;
       }
       $structure .= "</div>";
    }
    ############ END SHOW PRODUCTS STOCK ########### 
	
	if(!is_null($product->discount_price) && $product->discount_price == $product->lowest_price && $product->price != 0) {  // && $product->discount_price != 0
		$structure .= "<span class=\"vbl-product-price vbl-product-discounted\">$product->price $currency</span><br />";
		$structure .= "<span class=\"vbl-product-price vbl-product-discount\">$product->discount_price $currency</span>";
	}
	else {
		$structure .= "<span class=\"vbl-product-price vbl-product-discounted\"></span><br />";
		$structure .= "<span class=\"vbl-product-price vbl-product-discount\">$product->price $currency</span>";
	}
    
    //6  <div class="vbl-moms">Inkl. Moms<br /><br /><br /></div>|<div class="vbl-moms">Eksl. Moms<br /><br /><br /></div>
    //12 <div class="vbl-moms">Eks. Moms</div>|<div class="vbl-moms">Ink. Moms</div>'
   
    $vat_message = settings::get('vat_message',$this->wid);
	if($vat_message)
	{
	   $structure .= '<br />';
       if(strpos($vat_message,'|') !== false)
       {
           list($with_vat_msg ,$without_vat_msg) = explode('|',$vat_message);
       } 
       else 
       {
          $with_vat_msg =  '<div class="vbl-moms">Inkl. Moms</div>';
          $without_vat_msg = '<div class="vbl-moms">Eksl. Moms</div>';
       }
	   if($this->vubla_enable_vat)
	   {    
	       $structure .= $with_vat_msg;
       } 
       else 
       {
           $structure .= $without_vat_msg;  
       }

    }
    
    ############# END OF BADNESS 
    
    
	$structure .= "</div>
			<div class=\"vbl-buttons\">";
			
		if($this->attributes['More Info Button'] != 'none') 
		{
			if(false === strpos($this->attributes['More Info Button'],'http://')) {	
							$structure .= "<a class=\"vbl-more-info-button\" href=\"$product->link\">Mere info</a>";
			}
			else {
				$structure .= "<a class=\"vbl-more-info-button\" href=\"$product->link\"><img src=\"" . $this->attributes['More Info Button'] . "\"></a>";
			}
		}
if(settings::get('bgsys_buy_now_return_to_search',$wid)){
    $product->buy_link = $this->getShopLink(array('action'=>'buy_now', 'products_id'=>$product->pid));
}

if(settings::get('magento_buy_now_return_to_search',$wid)){
    $product->buy_link = $this->modifyBuyNowLink($product->buy_link,'/uenc/'.base64_encode($this->host).'/product/'.$product->pid.'/');
}
        
        
if(false === strpos($this->attributes['Buy Now Button'],'http://')) {
                                $structure .= "<a class=\"vbl-buy-now-button\" href=\"$product->buy_link\">Læg i kurv</a>";
}
else {
        if($this->host == 'www.made4men.dk'){  // They have magic JS url injection - Bastards!!
            $structure .= "<a class=\"vbl-buy-now-button\" onClick=\"setLocation('".$product->buy_link."');\" href=\"#\"><img src=\"" . $this->attributes['Buy Now Button'] . "\"></a>";
        } else {
            $structure .= "<a class=\"vbl-buy-now-button\"  href=\"$product->buy_link\"><img style=\"border: 0;\" src=\"" . $this->attributes['Buy Now Button'] . "\"></a>";
        }
          //$structure .= "<img class=\"vbl-buy-now-button\"  src=\"" . $this->attributes['Buy Now Button'] . "\" onClick=\"window.location.href ='".$product->buy_link."';\"";
}

			$structure .= "</div>
			</div>
		</li>";
		
	if($i == count($products)-1) {
		$structure .= "<div class=\"clear\"></div></ul>";
	}
	
	$i++;
	}
}
$structure .= "</div>";
?>
