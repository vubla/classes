<?php



require_once '../vublamailer.php';
require_once '../basedbtest.php';
$suite  = new PHPUnit_Framework_TestSuite("ProductTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);


$suite  = new PHPUnit_Framework_TestSuite("CrawlerTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);


class CrawlerTest extends BaseDbTest 
{
        function testT()
        {
            $this->assertTrue(true);
        }
}
    
