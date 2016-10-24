<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';

$suite  = new PHPUnit_Framework_TestSuite("ConjunctivestringfilterTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);

class StringFilterObject extends ConjunctiveStringFilter {
    function callsearch($words,$prod_ids) {
        return $this->search($words,$prod_ids);
    }
}



class ConjunctivestringfilterTest extends BaseDbTest 
{
    function setUp() {
        $this->wid = 1;
        $this->buildDatabases(); 
        $this->data = 'dvd';
        $this->searcher = new StringFilterObject($this->wid,$this->data,true);
        
        $this->setRank('dvd',10);
        $this->setRank('matrix',10);
        $this->setRank('matrox',10);
    }
    

    function tearDown() {
        unset($this->searcher);
        $this->dropDatabases();
        
    }
    
    private function setRank($word,$rank) {
        $this->shopVdo->exec('UPDATE words SET rank = '.$this->shopVdo->quote($rank).' WHERE word = '.$this->shopVdo->quote($word));
    }
    
    ###################
    // Search Method
    ###################
    function testEmptyIsReturnedFromSearchWhenNonArrayIsInserted() 
    {
        $searcher = new StringFilterObject($this->wid,'');
        $result = $searcher->callsearch(new stdClass(),null);
        $this->assertEmpty($result);
    }
    
    function testSomthingIsReturnedWhenSearchingForValidWord() 
    {
        $expected = array();
        $searcher = new StringFilterObject($this->wid,'');
        $result = $searcher->callsearch(array(new SearchWord('dvd')),null);
        $this->assertNotEquals(0,sizeof($result),"Size of result is 0");
    }
    
    function testProductIdInputSearchFailure() 
    {
        $searcher = new StringFilterObject(1,'');
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
        $searcher = new StringFilterObject(1,$this->data,false);
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
        $searcher = new StringFilterObject(1,$this->data,false);
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
        $searcher = new StringFilterObject(1,$this->data,false);
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
        $searcher = new StringFilterObject(1,'sadfghdjfkdgsaghsjdkhgfdfghdkjdhgfdasghjkfgfdas',true);
        $result = $searcher->getResults($ref);
        $this->assertCount(sizeof($expected),$result);
       
    }
    
    function testGetResultsSpellingCorrection() 
    {
        $ref = 'matris';
        $expected = 'matrix';
        $searcher = new StringFilterObject(1,$ref,true);
        $result = $searcher->getResults(array());
        $this->assertFalse(is_null(Settings::get('min_proximity_ratio',$this->wid)), 'The min_proximity_ratio is missing');
        $this->assertArrayHasProperty($expected,$searcher->words_i_search_for,'word');
        $this->assertTrue(sizeof($searcher->getCorrectedWords()) > 0);
    }
    
    function testGetResultsSpellingCorrection2() 
    {
        $ref = 'atrix';
        $expected = 'matrix';
        $searcher = new StringFilterObject(1,$ref,true);
        $result = $searcher->getResults(array());
        $this->assertFalse(is_null(Settings::get('min_proximity_ratio',$this->wid)), 'The min_proximity_ratio is missing');
        $this->assertArrayHasProperty($expected,$searcher->words_i_search_for,'word');
        $this->assertTrue(sizeof($searcher->getCorrectedWords()) > 0);
    }
    
    function testGetResultsSynonymCorrection() 
    {
        $ref = 'skive';
        $expected = 'dvd';
        $searcher = new StringFilterObject(1,$ref,true);
        $result = $searcher->getResults(array());
        $this->assertFalse(is_null(Settings::get('min_proximity_ratio',$this->wid)), 'The min_proximity_ratio is missing');
        $this->assertArrayHasProperty($expected,$searcher->words_i_search_for,'word');  
    }
    
    function testGetResultsSynonymCorrectionShowsSomethingInDidYouMean() 
    {
        $ref = 'matrix';
        $expected = 'matrox';
        $searcher = new StringFilterObject(1,$ref,true);
        $result = $searcher->getResults(array());
        $this->assertFalse(is_null(Settings::get('min_proximity_ratio',$this->wid)), 'The min_proximity_ratio is missing');
        $result = $searcher->get_did_you_mean();
        $this->assertCount(1,$result);
        $this->assertArrayHasProperty($expected,$result[0],'word'); //Notice that did_you_mean is a multi dimentional array  
    }
    
    function testGetResultsSynonymCorrectionGetsResult() 
    {
        $ref = 'skive';
        Settings::setLocal('min_search_results',1,$this->wid);
        $expected = array (5,19,10,15,6,20,11,16,7,12,17,8,13,4,18,9,14,2);
        $searcher = new StringFilterObject($this->wid,$ref,true);
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
       
        $searcher = new StringFilterObject(1,'',true);
        $result = $searcher->getResults($ref);
        $this->assertCount(28,$result);
        foreach ($result as $item) {
            $this->assertInternalType('int',$item);
        }
    }
    
    function testFindConjunctive() 
    {
        $ref = 'dvd courage';
        $searcher = new StringFilterObject($this->wid,$ref,true);
        $expected = array (16);
        $result = $searcher->getResults(array());
        $this->assertCount(sizeof($expected),$result);
        foreach ($expected as $item) 
        {
            $this->assertContains($item,$result);
        }
        $this->assertCount(0,$searcher->get_did_you_mean(),'Did you mean contains: '.print_r($searcher->get_did_you_mean(),true));
    }
    
    function testFindConjunctiveWithSpellingMistake() 
    {
        $ref = 'dbd courage';
        Settings::setLocal('min_search_results',1,$this->wid);
        $searcher = new StringFilterObject($this->wid,$ref,true);
        $expected = array (16);
        $result = $searcher->getResults(array());
        $this->assertCount(sizeof($expected),$result);
        foreach ($expected as $item) 
        {
            $this->assertContains($item,$result);
        }
        
        $expected1 = 'dvd';
        $this->assertCount(1,$searcher->get_did_you_mean(),'Did you mean contains: '.print_r($searcher->get_did_you_mean(),true));
        $result = $searcher->get_did_you_mean();
        $this->assertArrayHasProperty($expected1,$result[0],'word'); //Notice that did_you_mean is a multi dimentional array
    }
    
    function testFindConjunctiveWithSynonymMistake() 
    {
        $ref = 'skive courage';
        Settings::setLocal('min_search_results',1,$this->wid);
        $searcher = new StringFilterObject($this->wid,$ref,true);
        $expected = array (16);
        $result = $searcher->getResults(array());
        $this->assertCount(sizeof($expected),$result);
        foreach ($expected as $item) 
        {
            $this->assertContains($item,$result);
        }
        
        $expected1 = 'dvd';
        $this->assertCount(1,$searcher->get_did_you_mean(),'Did you mean contains: '.print_r($searcher->get_did_you_mean(),true));
        $result = $searcher->get_did_you_mean();
        $this->assertArrayHasProperty($expected1,$result[0],'word'); //Notice that did_you_mean is a multi dimentional array
    }
    
    function testFindConjunctiveWithManyWords() 
    {
        $ref = 'courage und fir english dvd';
        Settings::setLocal('min_search_results',1,$this->wid);
        $searcher = new StringFilterObject($this->wid,$ref,true);
        $expected = array (16);
        $result = $searcher->getResults(array());
        $this->assertCount(sizeof($expected),$result);
        foreach ($expected as $item) 
        {
            $this->assertContains($item,$result);
        }
    }
    
    function testFindConjunctiveWithManyWordsAndSpellingMistakes() 
    {
        $this->setRank('courage', 1);
        $this->setRank('und', 1);
        $this->setRank('fir', 1);
        $this->setRank('english', 1);
        $ref = 'courager und fire englis dvd';
        Settings::setLocal('min_search_results',1,$this->wid);
        $searcher = new StringFilterObject($this->wid,$ref,true);
        $expected = array (16);
        $result = $searcher->getResults(array());
        $this->assertCount(sizeof($expected),$result);
        foreach ($expected as $item) 
        {
            $this->assertContains($item,$result);
        }
        $expectedDidYouMeans = array();
        $expectedDidYouMeans[] = 'courage';
        $expectedDidYouMeans[] = 'und';
        $expectedDidYouMeans[] = 'fir';
        $expectedDidYouMeans[] = 'english';
        $expectedDidYouMeans[] = 'dvd';
        $result = $searcher->get_did_you_mean();
        $this->assertCount(1,$result,'Did you mean contains: '.print_r($searcher->get_did_you_mean(),true));
        $this->assertCount(sizeof($expectedDidYouMeans),$result[0],'Did you mean contains: '.print_r($searcher->get_did_you_mean(),true));
        foreach ($expectedDidYouMeans as $expected) {
            $this->assertArrayHasProperty($expected,$result[0],'word'); //Notice that did_you_mean is a multi dimentional array
        }
    }
    
    function testGetRelatedSearches() 
    {
        $ref = 'dvd matrix matrox derpaderpdiderpeliderp';
        Settings::setLocal('min_search_results',1,$this->wid);
        $searcher = new StringFilterObject($this->wid,$ref,true);
        $expected = array ();
        $result = $searcher->getResults(array());
        $this->assertCount(sizeof($expected),$result);
        foreach ($expected as $item) 
        {
            $this->assertContains($item,$result);
        }
        $expectedRelated = array('dvd','matrix','matrox');
        $result = $searcher->get_related_searches();
        $this->assertCount(sizeof($expectedRelated),$result,'Related seraches contains: '.print_r($searcher->get_did_you_mean(),true));
        foreach ($expectedRelated as $expected) {
            $this->assertArrayHasProperty($expected,$result,'word'); //Notice that did_you_mean is a multi dimentional array
        }
    }
    
    function testFindConjunctiveWithCharactersSwapped() 
    {
        $ref = 'courage ddv';
        Settings::setLocal('min_search_results',1,$this->wid);
        $searcher = new StringFilterObject($this->wid,$ref,true);
        $expected = array (16);
        $result = $searcher->getResults(array());
        $this->assertCount(sizeof($expected),$result);
        foreach ($expected as $item) 
        {
            $this->assertContains($item,$result);
        }
        $expectedDidYouMeans = array();
        $expectedDidYouMeans[] = 'courage';
        $expectedDidYouMeans[] = 'dvd';
        $result = $searcher->get_did_you_mean();
        $this->assertCount(1,$result,'Did you mean contains: '.print_r($searcher->get_did_you_mean(),true));
        $this->assertCount(sizeof($expectedDidYouMeans),$result[0],'Did you mean contains: '.print_r($searcher->get_did_you_mean(),true));
        foreach ($expectedDidYouMeans as $expected) {
            $this->assertArrayHasProperty($expected,$result[0],'word'); //Notice that did_you_mean is a multi dimentional array
        }
    }
    
    function testFindSomethingWhenOneWordIsNotValid() 
    {
        $ref = 'dvd courage johnsonsbaybaby';
        Settings::setLocal('min_search_results',1,$this->wid);
        $searcher = new StringFilterObject($this->wid,$ref,true);
        $expected = array (16);
        $result = $searcher->getResults(array());
        $this->assertCount(sizeof($expected),$result);
        foreach ($expected as $item) 
        {
            $this->assertContains($item,$result);
        }
        /*
        $expectedDidYouMeans = array();
        $expectedDidYouMeans[] = 'courage';
        $expectedDidYouMeans[] = 'dvd';
        $result = $searcher->get_did_you_mean();
        $this->assertCount(1,$result,'Did you mean contains: '.print_r($searcher->get_did_you_mean(),true));
        $this->assertCount(sizeof($expectedDidYouMeans),$result[0],'Did you mean contains: '.print_r($searcher->get_did_you_mean(),true));
        foreach ($expectedDidYouMeans as $expected) {
            $this->assertArrayHasProperty($expected,$result[0],'word'); //Notice that did_you_mean is a multi dimentional array
        }
        */
    }
}