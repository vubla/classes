<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';

$suite  = new PHPUnit_Framework_TestSuite("StringfilterTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);

class StringFilterObject1 extends StringFilter {
    function callexpandTowardsSpellingMistakes($word) {
        return $this->expandTowardsSpellingMistakes($word);
    }
    
    function callexpandTowardsSynonyms($word) {
        return $this->expandTowardsSynonyms($word);
    }
    
    function callsearch($words,$prod_ids) {
        return $this->search($words,$prod_ids);
    }
    
    function callgetBestWord($words) {
        return $this->getBestWord($words);
    }
}



class StringfilterTest extends BaseDbTest 
{
    var $wid = 1;
    function setUp() {
        //$this->resetDatabases();
        $this->buildDatabases(); 
        $this->data = 'dvd';
        $this->searcher = new StringFilter($this->wid,$this->data,true);
       

    }

    function tearDown() {
        unset($this->searcher);
        $this->dropDatabases();
        
    }
    
    ########################################
    /// Expand Towards Spelling Mistakes
    ########################################
    function testExpandTowardsSpellingMistakesSucces() 
    {
        $data = new SearchWord('matris');
        $searcher = new StringFilterObject1(1,'');
        $result = $searcher->callexpandTowardsSpellingMistakes($data);
        $this->assertCount(1,$result,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
        $this->assertEquals('matrix',$result[0]->word,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
    }
    /*
     * Tempory out. We deem it should fail. 
    function testExpandTowardsSpellingMistakesFailure() 
    {
        $data = new SearchWord('dvd');
        $searcher = new StringFilterObject1('');
        $result = $searcher->callexpandTowardsSpellingMistakes($data);
        $this->assertCount(0,$result,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
    }*/
    
    ################################
    /// Expand Towards Synonyms
    ################################
    function testExpandTowardsSynonymsSucces() 
    {
        $data = new SearchWord('skive');
        $searcher = new StringFilterObject1(1,'');
        $result = $searcher->callexpandTowardsSynonyms($data);
        $this->assertArrayHasProperty('dvd',$result,'word');
    }
    
    function testExpandTowardsSynonymsFailure() 
    {
        $data = new SearchWord('dvd');
        $searcher = new StringFilterObject1(1,'');
        $result = $searcher->callexpandTowardsSynonyms($data);
        $this->assertCount(0,$result,"result: ".print_r($result,true)." on '".print_r($data,true)."'");
    }
    
    ####################
    // Get Best Word
    ####################
    function testGetBestWordSingleValidInput()
    {
        $expected = new SearchWord('dvd');
        $data = array($expected);
        $searcher = new StringFilterObject1(1,'');
        $result = $searcher->callgetBestWord($data);
        $this->assertEquals($expected,$result);
    }
    
    function testGetBestWordSingleInvalidInput()
    {
        $data = array(new SearchWord('asddfsfdsa'));
        $searcher = new StringFilterObject1(1,'');
        $result = $searcher->callgetBestWord($data);
        $this->assertNull($result);
    }
    
    function testGetBestWordMultipleValidInput()
    {
        $expected = new SearchWord('dvd');
        $data = array(new SearchWord('g200'),
            new SearchWord('matrox'),
            $expected,
            new SearchWord('matrix')
        );
        $searcher = new StringFilterObject1(1,'');
        $result = $searcher->callgetBestWord($data);
        $this->assertEquals($expected,$result);
    }
    
    function testGetBestWordMultipleInvalidInput()
    {
        $data = array(new SearchWord('asddfsfdsa'),
            new SearchWord('luhgltrh'),
            new SearchWord('suygeusfdf'),
            new SearchWord('iuhgl')
        );
        $searcher = new StringFilterObject1(1,'');
        $result = $searcher->callgetBestWord($data);
        $this->assertNull($result);
    }
    
    function testGetBestWordMultipleMixedInput()
    {
        $expected = new SearchWord('dvd');
        $data = array(new SearchWord('asdasdgfgre'),
            new SearchWord('matrox'),
            $expected,
            new SearchWord('johnson')
        );
        $searcher = new StringFilterObject1(1,'');
        $result = $searcher->callgetBestWord($data);
        $this->assertEquals($expected,$result);
    }
    
    function testGetBestBugExperiencedTest()
    {
        $expected = new SearchWord('dvd');
        $data = array(
            $expected,
            new SearchWord('skive')
        );
        $searcher = new StringFilterObject1(1,'');
        $result = $searcher->callgetBestWord($data);
        $this->assertEquals($expected,$result);
    }
    
    ###################
    // Search Method
    ###################
    function testEmptyIsReturnedFromSearchWhenNonArrayIsInserted() 
    {
        $searcher = new StringFilterObject1(1,'');
        $result = $searcher->callsearch(new stdClass(),null);
        $this->assertEmpty($result);
    }
    
    function testSomthingIsReturnedWhenSearchingForValidWord() 
    {
        $expected = array();
        $searcher = new StringFilterObject1(1,'');
        $result = $searcher->callsearch(array(new SearchWord('dvd')),null);
        $this->assertNotEquals(0,sizeof($result),"Size of result is 0");
    }
    
    function testProductIdInputSearchFailure() 
    {
        $searcher = new StringFilterObject1(1,'');
        $ref = array(1,3,21);
        $expected = array ();
        $result = $searcher->callsearch(array(new SearchWord($this->data)), $ref);
        $this->assertCount(sizeof($expected),$result,"result: ".print_r($result,true)." on input: ".print_r($ref,true));
    }
    
    ##################
    // GetResult
    ##################
    function testGetSomethingFromGetResults() 
    {
        $ref = array();
        $result = $this->searcher->getResults($ref);
        $this->assertInternalType('array',$result);
        foreach ($result as $item) 
        {
            $this->assertInternalType('int',$item);
        }
    }

    function testGetCorrectNumberFromGetResults() 
    {
        $ref = array();
        $expected = array (5,19,10,15,6,20,11,16,7,12,17,8,13,4,18,9,14,2);
        $result = $this->searcher->getResults($ref);
        $this->assertCount(sizeof($expected),$result);
        foreach ($expected as $item) 
        {
            $this->assertContains($item,$result);
        }
    }
    
    function testProductIdInputGetResultsSucces() 
    {
        $searcher = new StringFilter(1,$this->data,false);
        $ref = array(5,19,10);
        $expected = array (5,19,10);
        $result = $searcher->getResults($ref);
        $this->assertCount(sizeof($expected),$result);
        foreach ($expected as $item) {
            $this->assertContains($item,$result);
        }
    }
    
    function testProductIdInputGetResultsFailure() 
    {
        $searcher = new StringFilter(1,$this->data,false);
        $ref = array(1,3,21);
        $expected = array ();
        $result = $searcher->getResults($ref);
        $this->assertCount(sizeof($expected),$result,"result: ".print_r($result,true));
        foreach ($expected as $item) { // yeah i know
            $this->assertContains($item,$result);
        }
    }
    
    function testProductIdInputGetResultsMix() 
    {
        $searcher = new StringFilter(1,$this->data,false);
        $ref = array(5,3,21,1,19,10);
        $expected = array (5,19,10);
        $result = $searcher->getResults($ref);
        $this->assertCount(sizeof($expected),$result);
        foreach ($expected as $item) {
            $this->assertContains($item,$result);
        }
    }
    
   function testGetNothingWhenNothingShouldBeFound() 
   {
        $ref = array();
        $expected = array ();
        $searcher = new StringFilter(1,'sadfghdjfkdgsaghsjdkhgfdfghdkjdhgfdasghjkfgfdas',true);
        $result = $searcher->getResults($ref);
        $this->assertCount(sizeof($expected),$result);
       
    }
    
    function testGetResultsSpellingCorrection() 
    {
        $ref = 'matris';
        $expected = 'matrix';
        $searcher = new StringFilter(1,$ref,true);
        $result = $searcher->getResults(array());
        $this->assertFalse(is_null(Settings::get('min_proximity_ratio',$this->wid)), 'The min_proximity_ratio is missing');
        $this->assertArrayHasProperty($expected,$searcher->words_i_search_for,'word'); 
    }
    
    function testGetResultsSpellingCorrection2() 
    {
        $ref = 'atrix';
        $expected = 'matrix';
        $searcher = new StringFilter(1,$ref,true);
        $result = $searcher->getResults(array());
        $this->assertFalse(is_null(Settings::get('min_proximity_ratio',$this->wid)), 'The min_proximity_ratio is missing');
        $this->assertArrayHasProperty($expected,$searcher->words_i_search_for,'word');  
    }
    
    function testGetResultsSynonymCorrection() 
    {
        $ref = 'skive';
        $expected = 'dvd';
        $searcher = new StringFilter(1,$ref,true);
        $result = $searcher->getResults(array());
        $this->assertFalse(is_null(Settings::get('min_proximity_ratio',$this->wid)), 'The min_proximity_ratio is missing');
        $this->assertArrayHasProperty($expected,$searcher->words_i_search_for,'word');  
    }
    
    function testGetResultsSynonymCorrectionShowsSomethingInDidYouMean() 
    {
        $ref = 'matrix';
        $expected = 'matrox';
        $searcher = new StringFilter(1,$ref,true);
        $result = $searcher->getResults(array());
        $this->assertFalse(is_null(Settings::get('min_proximity_ratio',$this->wid)), 'The min_proximity_ratio is missing');
        $this->assertArrayHasProperty($expected,$searcher->did_you_mean[0],'word'); //Notice that did_you_mean is a multi dimentional array  
    }
    
    function testGetResultsSynonymCorrectionGetsResult() 
    {
        $ref = 'skive';
        $expected = array (5,19,10,15,6,20,11,16,7,12,17,8,13,4,18,9,14,2);
        $searcher = new StringFilter(1,$ref,true);
        $result = $searcher->getResults(array());
        $this->assertCount(sizeof($expected),$result);
        foreach ($expected as $item) 
        {
            $this->assertContains($item,$result);
        }
    }
    
    function testFindEverything() 
    {
        $ref = array();
       
        $searcher = new StringFilter(1,'',true);
        $result = $searcher->getResults($ref);
        $this->assertCount(28,$result);
        foreach ($result as $item) {
            $this->assertInternalType('int',$item);
        }
    }
}