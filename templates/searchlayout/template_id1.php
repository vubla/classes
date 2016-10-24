<?php
$structure = "<style type=\"text/css\"> 
	#vbl-serp {
		font-family: " . $this->attributes['Font Stack'] . ";
		color: " . $this->attributes['Text Color'] . ";
	}
	
	#vbl-userdefined-keywords {
		margin: 0;

		padding: 0;

		font-size: " . (12*($this->attributes['Font Size']/100)+12) . "px;

		list-style: none;	
	}

	#vbl-product-list {
		margin: 0;

		padding: 0;

		font-size: " . (12*($this->attributes['Font Size']/100)+12) . "px;

		list-style: none;
	}

	#vbl-product-list li {
		min-height: 20px;

		margin: 0;

		padding: 10px 10px 10px 5px;

		overflow: auto;

		border-bottom: 1px solid " . $this->attributes['Secondary Color'] . ";

		clear: both;
	}

	.vbl-product-img {
		height: ".$pictureHeight."px;
		width: auto;
		
		margin: 0px 10px 0px 0px;

		padding: 0;

		float: left;
	}

	.vbl-product-name {
		margin: 0;

		padding: 0;

		font-size: " . (15*($this->attributes['Font Size']/100)+15) . "px;
		font-weight: bold;
	}
	
	a.vbl-product-name:hover {
        ".Settings::get('extra_hover_name_style',$this->wid)."
    }

	.vbl-product-cost {
		margin: 5px 0px 10px 0px;

		padding: 0;

		font-weight: bold;
	}

	.vbl-product-discounted {
		margin: 0;

		padding: 0;

		text-decoration: line-through;
		font-size: " . (10*($this->attributes['Font Size']/100)+10) . "px;
	}

	.vbl-product-discount {
		margin: 0;

		padding: 0;

		font-size: " . (12*($this->attributes['Font Size']/100)+12) . "px;
		color: red;
	}
	
	.vbl-product-description {
		margin: 10px 0px 0px 0px;

		padding: 0;

		font-size: " . (9*($this->attributes['Font Size']/100)+9) . "px;
	}

	.vbl-buttons {
		height: 30px;
		float: right;
	}

	.vbl-buy-now-button {
		\n\n";
		
if(false === strpos($this->attributes['Buy Now Button'],'http://')) {
	$structure .= "
		height: 27px;
		width: 71px;

		display: inline-block;
		
		text-indent: -9999px;
		
		background: url('" . API_URL . "/images/btn_buy_" . $this->attributes['Buy Now Button'] . ".png');
	}
	
	.vbl-buy-now-button:hover, .vbl-buy-now-button:active {
		background-position: -71px 0px;\n";
}

	$structure .= "}
	
	.vbl-more-info-button {
		margin-right: 10px;\n\n";
		
if(false === strpos($this->attributes['More Info Button'],'http://')) {
	$structure .= "
		height: 27px;
		width: 71px;
		
		display: inline-block;

		text-indent: -9999px;
		
		background: url('" . API_URL . "/images/btn_info_" . $this->attributes['More Info Button'] . ".png');		
	}
	
	.vbl-more-info-button:hover, .vbl-more-info-button:active {
		background-position: -71px 0px;\n";
}
$structure .= "}\n\n";
	
$structure .= ".clear { clear: both; }
</style>

<div id=\"vbl-serp\">";

if(empty($products))
{
    $structure .= 
    "<div id=\"vbl-no-result\">
        <div id=\"vbl-no-result-headline\">Fandt du ikke varen du søgte?</div>
    
        <div id=\"vbl-no-result-box\">
            <div id=\"vbl-no-result-text\">
                " . $emptyTemplateText .  "
            </div>
            <img id=\"vbl-photo\" src=\"http://api.vubla.com/images/photo.jpg\" alt=\"Photo\" />
        </div>
    </div>";
}
else
{
    $structure .= "<ul id=\"vbl-product-list\">";
    foreach($products as $product) {
    
    	$structure .= "<li data-price=\"";
    
        if(!is_null($product->discount_price) && $product->discount_price != 0) {
        	 $structure .= $product->discount_price . "\">";
        }
        else {
        	 $structure .= $product->price . "\">";
        }
    	$structure .= "<div class=\"vbl-buttons\">
			<a class=\"vbl-buy-now-button\" href=\"$product->buy_link\">";
    	
    	if(false !== strpos($this->attributes['Buy Now Button'],'http://')) {
    		$structure .= "<img src=\"".$this->attributes['Buy Now Button']."\" alt=\"Læg i kurv\" />";
    	}
    	else {
    		$structure .= "Læg i kurv";
    	}
		$structure .= "</a>
			<a class=\"vbl-more-info-button\" href=\"$product->link\">";
	
    	if(false !== strpos($this->attributes['More Info Button'],'http://')) {
    		$structure .= "<img src=\"".$this->attributes['More Info Button']."\" alt=\"Mere info\" />";
    	}
    	else {
    		$structure .= $this->attributes['More Info Button']."Mere info";
    	}	
		$structure .= "</a>";
	
    		
    	$structure .= "
		</div>
    		<img class=\"vbl-product-img\" src=\"$product->image_link\" alt=\"$product->name\" />
    		<!--<div class=\"img\"></div>-->
    		<a class=\"vbl-product-name\" href=\"$product->link\">$product->name</a><br />
    		<div class=\"vbl-product-cost\">";
    

        if(!is_null($product->discount_price) && $product->discount_price == $product->lowest_price && $product->price != 0) {  // && $product->discount_price != 0
        	$structure .= "<span class=\"vbl-product-price vbl-product-discounted\">Pris: $product->price $currency</span><br />";
        	$structure .= "Tilbud: <span class=\"vbl-product-price vbl-product-discount\">$product->discount_price $currency</span>";
        }
        else {
        	$structure .= "Pris: <span class=\"vbl-product-price\">$product->price $currency</span>";
        }
        
        $structure .= "</div>	
        		<p class=\"vbl-product-description\">
        			$product->description
        		</p>
        	    </li>";
    }
    $structure .=  "</ul>\n";
    $structure .=  "</div>";
}

?>
