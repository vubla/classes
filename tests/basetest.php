<?php

require_once '../config.php';

class BaseTest extends PHPUnit_Framework_TestCase {
    
    function daq($toDump)
    {
        var_dump($toDump);
        exit;
    }   
    function testDummy()
    {
        $this->assertTrue(true);
    } 
    function assertArrayHasProperty($expected, $array, $property, $msg = ''){
        $this->assertArrayProperties('assertTrue',$expected, $array, $property, $msg);
    }
    function assertArrayHasNotProperty($expected, $array, $property, $msg = ''){
        $this->assertArrayProperties('assertFalse',$expected, $array, $property, $msg);
    }

    private function assertArrayProperties($func, $expected, $array, $property, $msg = ''){
        $foundIt = false;
        foreach ($array as $value) {
            if($value->$property == $expected){
                $foundIt = true;
            }
        }
       
        $this->$func($foundIt, 'Array '. print_r($array, true).' did not have a property "'. $property . '" matching ' . $expected,$msg);
        
    }
}


?>
