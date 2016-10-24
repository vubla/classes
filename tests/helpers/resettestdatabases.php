<?php


if(isset($_SERVER['argv'][2])) {
    define('WID', $_SERVER['argv'][2]);
}
require '../basedbtest.php';

$suite  = new PHPUnit_Framework_TestSuite("ResetDb");

class ResetDb extends BaseDbTest 
{
    function setUp() {
        //$this->resetDatabases();
    }
    
    function testReset()
    {
        $this->resetDatabases();
    }
}

?>