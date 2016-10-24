<?php



//define ('DB_METADATA', 'phpunit_metadata');
require_once '../vublamailer.php';
require_once '../basedbtest.php';


$suite  = new PHPUnit_Framework_TestSuite("JavaScriptGeneratorTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);

class DummyWidget extends Widget
{
    var $wid = 1;
    function __construct() {
        parent::__construct($this->wid,11232);
    }
    function generateJS() {
        return 'var testVariable = 1;';
    }
    
    function generateHtml() {
        return '';
    }
}



class JavaScriptGeneratorTest extends BaseDbTest 
{
     var $wid = 1;
    function setUp() {
        $this->buildDatabases();
    }
    
    function tearDown() {
        $this->dropDatabases();
    }
    
    function testConstruct() {
        $jSgen = new JavaScriptGenerator($this->wid);
        $this->assertNotNull($jSgen);
    }
    
    function testGenerateNoWidget() {
        $jSgen = new JavaScriptGenerator($this->wid);
        
        $actual = $jSgen->generate();
        
        $this->assertContains('var vubla_base',$actual);
        $this->assertContains('RunOnRefresh',$actual);
        $this->assertContains('AjaxFullSearch',$actual);
        $this->assertNotContains('testVariable',$actual); // no widgets generated
    }

    function testGenerateWithWidget() {
        $jSgen = new JavaScriptGenerator($this->wid);
        
        $actual = $jSgen->generate(array(new DummyWidget()));
        
        $this->assertContains('var vubla_base',$actual);
        $this->assertContains('RunOnRefresh',$actual);
        $this->assertContains('AjaxFullSearch',$actual);
        $this->assertContains('testVariable',$actual); // widgets generated
    }
    
    function testGenerateWithInvalidInput() {
        $jSgen = new JavaScriptGenerator($this->wid);
        $input = 'some string';
        try {
            $actual = $jSgen->generate($input);
            $this->fail('No exception was thrown on input: ' . $input . '. Should have received an array of widgets or thrown an exception.');
        } catch(exception $e) {
        }
        
        //Just in case, verify that the jsgen is not broken
        $actual = $jSgen->generate();
        
        $this->assertContains('var vubla_base',$actual);
    }
    
    function testGenerateSuggestion() {
        $jSgen = new JavaScriptGenerator($this->wid);
        Settings::setLocal('autocomplete','1',$this->wid);
        
        $actual = $jSgen->generateSuggestion();
        
        $this->assertContains('var vubla_base',$actual);
        $this->assertContains('getSuggestions',$actual);
        $this->assertContains('#vbl-search-field',$actual);
        $this->assertNotContains('AjaxFullSearch',$actual); // This should not be in the suggestion
        $this->assertNotContains('testVariable',$actual); // widgets generated
        $this->assertNotContains('#search_field',$actual); // setting for custom searchfield not set
    }
    
    function testGenerateSuggestionWithCustomSearchField() {
        $jSgen = new JavaScriptGenerator($this->wid);
        Settings::setLocal('search_field_identifiers','#search_field',$this->wid);
        Settings::setLocal('autocomplete','1',$this->wid);
        
        $actual = $jSgen->generateSuggestion();
        
        $this->assertContains('#vbl-search-field',$actual);
        $this->assertContains('#search_field',$actual); // setting for custom searchfield set
    }
}






