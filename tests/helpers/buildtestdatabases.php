<?php


if(isset($_SERVER['argv'][2])) {
    define('WID', $_SERVER['argv'][2]);
}

require '../basedbtest.php';

$suite  = new PHPUnit_Framework_TestSuite("BuildDb");

class BuildDb extends BaseDbTest 
{
    function setUp() {
    }
    
    function testBuild()
    {
        $this->buildDatabases();
    }
}

?>