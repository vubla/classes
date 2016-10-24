<?php
require_once '../vublamailer.php';
require_once '../basetest.php';
$suite  = new PHPUnit_Framework_TestSuite("TextTest");



class TextTest extends BaseTest 
{
    function setUp() {
      
    
    }

    function tearDown() {
     
    } 
    
    function test_(){
        
        $data = 'Primary Color';
        $result = Text::_($data,'someobscureclass');
        $expected = primary_color;
              //  throw new Exception($expected);
        $this->assertEquals($expected, $result);
        
        $result = Text::_('url');
        $expected = url;
        $this->assertEquals($expected, $result);
        
        $data = 'somethingreallyobsDDSSDDSDSDS urdedthatreallyshouldnotbeinthesadasddsaads';
        $result = Text::_($data);
        $expected = $data;
        $this->assertEquals($expected, $result);
        
        $data = 'Primary Color';
        $result = Text::_($data);
        $expected = primary_color;
        $this->assertEquals($expected, $result);
        
        $data = 'not';
        $result = Text::_($data, 'optionsSettings');
        $expected = optionssettings_not;
        $this->assertEquals($expected, $result);
    }
    
    
    function testSubstrWord(){
        $data = 'Hejs meds dig jeg er en streng';
        $result = Text::substrword($data, 5);
        $expected = 'Hejs...';
        $this->assertEquals($expected, $result);
        
        $data = 'Hej med dig jeg er en streng';
        $result = Text::substrword($data, 5);
        $expected = 'Hej...';
        $this->assertEquals($expected, $result);
        
        $data = 'Hej';
        $result = Text::substrword($data, 5);
        $expected = 'Hej';
        $this->assertEquals($expected, $result);
        
        $data = 'Hejsa';
        $result = Text::substrword($data, 5);
        $expected = 'Hejsa';
        $this->assertEquals($expected, $result);
        
        $data = 'Hejsa';
        $result = Text::substrword($data, 4);
        $expected = '...';
        $this->assertEquals($expected, $result);
        
        $data = 'Hej med dig jeg er en streng';
        $result = Text::substrword($data, 0);
        $expected = '';
        $this->assertEquals($expected, $result);
        
        $data = 'Hej med dig jeg er en streng';
        $result = Text::substrword($data, 1220);
        $expected = 'Hej med dig jeg er en streng';
        $this->assertEquals($expected, $result);
    }
}


