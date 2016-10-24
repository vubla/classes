<?php

class CategoryWidget extends Widget {
    
    public $categories;
    
    public function __construct($wid,$id){
        parent::__construct($wid,$id);
    }
    
    function generateHtml(){
        
        $catset = new CategorySet($this->wid);
        $catset->fillFromData($this->categories);
        $tree = $catset->getTreeList();
        
        $out = "<div id=\"vbl-widget-" . $this->id . "\" class=\"vbl-sidebar-widget\">";
   
        foreach ($tree as  $node) {
             //var_dump($this->eq_option['category']);    
             $checked_parent = ( isset($this->eq_option['category_id']) && in_array($node->cid,$this->eq_option['category_id'] )) ? 'checked' : '';
       //      var_dump($node->cid.' ' . print_r($this->eq_option['category'],true) );
        //     echo $checked_parent;
             $out .=  ' 
             			<div class="vbl-category vbl-parent">
	              			<span>
		             			<input type="checkbox" name="eq_options[category_id][]" value="'.$node->cid.'" '.$checked_parent.'/>
		             			</span>
		             			<span>
		             			&nbsp;<b>'.$node->name.'</b>
	             			</span>
	             			<span style="'.(($this->host == "dev.med24.dk" || $this->host == "med24.dk")? 'padding-top: 2.9px;': '') .'float: right; color: gray; font-size: 90%;">
	             				<b>('.$node->number_of_products.')</b>
	             			</span>
	             		</div>';
             
             foreach ($node->child as  $child)
             {
                 
                  $checked_child = ( isset($this->eq_option['category_id']) && in_array($child->cid.'@'.$node->cid,$this->eq_option['category_id'] ) )? 'checked' : '';
                  $out .=  ' 
                  			<div class="vbl-category vbl-child">
	              				<span >
		              				<input type="checkbox" name="eq_options[category_id][]" value="'.$child->cid.'@'.$node->cid.'" '.$checked_child.'/>
		              				&nbsp;'.$child->name.'
		              			</span>
	              				<span style="'.(($this->host == "dev.med24.dk" || $this->host == "med24.dk")? 'padding-top: 2.9px;': '') .'float: right; color: gray; font-size: 90%;">
	              					('.$child->number_of_products.')
	              				</span>
              				</div>';
             } 
        
             
        }
      //  exit;
       
        $out .= ' <div class="vbl-remove-with-js" style="text-align:right;"><input type="submit" value=" Opdater "/>';
        $out .= "</div></div>";
        return $out;
        // Here you can also use $this->cid OF SOME OPTION
    }
     
    function generateJS() {
        
        return  '
                    
                ';
         
    }

   function generateRefreshJS() {
       return '';
   }
}



?>
