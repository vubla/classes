<?php


require_once '../vublamailer.php';
require_once '../basedbtest.php';
$suite  = new PHPUnit_Framework_TestSuite("CategorySetTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);



class CategorySetTest extends BaseDbTest 
{
   
    function setUp() {
         $this->buildDatabases();
       
      
    }
    
    function tearDown() {

          $this->dropDatabases();
    }
    
    function testRemoveParents(){
    	$cats = array();
		$i = 0;
        $cats[++$i] = new Category(1);
        $cats[$i]->name = "Root";
		$cats[$i]->cid = 1;
		$cats[$i]->parent_id = 0;
		$cats[++$i] = new Category(1);
        $cats[$i]->name = "Defaul";
		$cats[$i]->cid = 2;
		$cats[$i]->parent_id = 1;
		$cats[++$i] = new Category(1);
        $cats[$i]->name = "John";
		$cats[$i]->cid = 3;
		$cats[$i]->parent_id = 2;
		$cats[++$i] = new Category(1);
        $cats[$i]->name = "Humb";
		$cats[$i]->cid = 4;
		$cats[$i]->parent_id = 2;
		$cats[++$i] = new Category(1);
        $cats[$i]->name = "Johnny";
		$cats[$i]->cid = 5;
		$cats[$i]->parent_id = 2;
		
		
		$set = new CategorySet(1);
		$set->fillFromData($cats);
		
		$set->removeCategories(array('Root', 'Defaul'));
		
		$this->assertCount(3, $set);
		foreach($set as $cat){
			$this->assertEquals(0,$cat->parent_id );
		}
    }
   
     function testRemoveParents2(){
    	$cats = array();
		$i = 0;
        $cats[++$i] = new Category(1);
        $cats[$i]->name = "Root";
		$cats[$i]->cid = 1;
		$cats[$i]->parent_id = 0;
		$cats[++$i] = new Category(1);
        $cats[$i]->name = "Defaul";
		$cats[$i]->cid = 2;
		$cats[$i]->parent_id = 1;
		$cats[++$i] = new Category(1);
        $cats[$i]->name = "John";
		$cats[$i]->cid = 3;
		$cats[$i]->parent_id = 2;
		$cats[++$i] = new Category(1);
        $cats[$i]->name = "Humb";
		$cats[$i]->cid = 4;
		$cats[$i]->parent_id = 2;
		$cats[++$i] = new Category(1);
        $cats[$i]->name = "Johnny";
		$cats[$i]->cid = 5;
		$cats[$i]->parent_id = 2;
		
		
		$set = new CategorySet(1);
		$set->fillFromData($cats);
		
		$set->removeCategories(array('Defaul'));
		
		$this->assertCount(4, $set);
		foreach($set as $cat){
			if($cat->name == 'Root'){
				$this->assertEquals(0,$cat->parent_id );
			} else {
				$this->assertEquals(1,$cat->parent_id );
			}
		}
    }
 

    function testRemoveParents3(){
    	$cats = array();
		$i = 0;
        $cats[++$i] = new Category(1);
        $cats[$i]->name = "Root";
		$cats[$i]->cid = 1;
		$cats[$i]->parent_id = 0;
		$cats[++$i] = new Category(1);
        $cats[$i]->name = "Defaul";
		$cats[$i]->cid = 2;
		$cats[$i]->parent_id = 1;
		$cats[++$i] = new Category(1);
        $cats[$i]->name = "John";
		$cats[$i]->cid = 3;
		$cats[$i]->parent_id = 2;
		$cats[++$i] = new Category(1);
        $cats[$i]->name = "Humb";
		$cats[$i]->cid = 4;
		$cats[$i]->parent_id = 2;
		$cats[++$i] = new Category(1);
        $cats[$i]->name = "Johnny";
		$cats[$i]->cid = 5;
		$cats[$i]->parent_id = 2;
		
		
		$set = new CategorySet(1);
		$set->fillFromData($cats);
		
		$set->removeCategories(array('Root'));
		
		$this->assertCount(4, $set);
		foreach($set as $cat){
			if($cat->name == 'Defaul'){
				$this->assertEquals(0,$cat->parent_id );
			} else {
				$this->assertEquals(2,$cat->parent_id );
			}
		}
    }
 
	
    function testRemoveParents4(){
    	$cats = array();
		$i = 0;
        $cats[++$i] = new Category(1);
        $cats[$i]->name = "Root";
		$cats[$i]->cid = 1;
		$cats[$i]->parent_id = 0;
		$cats[++$i] = new Category(1);
        $cats[$i]->name = "Defaul";
		$cats[$i]->cid = 2;
		$cats[$i]->parent_id = 1;
		$cats[++$i] = new Category(1);
        $cats[$i]->name = "John";
		$cats[$i]->cid = 3;
		$cats[$i]->parent_id = 2;
		$cats[++$i] = new Category(1);
        $cats[$i]->name = "Humb";
		$cats[$i]->cid = 4;
		$cats[$i]->parent_id = 3;
		$cats[++$i] = new Category(1);
        $cats[$i]->name = "Johnny";
		$cats[$i]->cid = 5;
		$cats[$i]->parent_id = 3;
		
		$cats[++$i] = new Category(1);
        $cats[$i]->name = "Point to 5";
		$cats[$i]->cid = 6;
		$cats[$i]->parent_id = 5;
		
		$cats[++$i] = new Category(1);
        $cats[$i]->name = "Point to 5";
		$cats[$i]->cid = 7;
		$cats[$i]->parent_id = 5;
		
		$set = new CategorySet(1);
		$set->fillFromData($cats);
		$set->removeCategories(array('Root','Defaul','John'));
			
		$this->assertCount(4, $set);
		foreach($set as $cat){
				if($cat->name == "Point to 5"){
					$this->assertEquals(5, $cat->parent_id);
				} else {
					$this->assertEquals(0,$cat->parent_id );
				}
			
		}
    }
 
    
}


