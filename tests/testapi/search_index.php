<?


require '../vublamailer.php';
require '../basedbtest.php';


$suite  = new PHPUnit_Framework_TestSuite("SearchTest");
/**
*
*
*/
class SearchTest extends BaseDbTest {

    var $command = 'cd ../helpers/folder; php ../../searchclitest.php ';
     function setUp() 
    {
        $this->buildDatabases();
        $_GET = array();
    }

    function tearDown() 
    {
        $this->dropDatabases();
        $_GET = array();
    }
    
  
  
    function testIndexFailure(){
        
        ob_start();
        exec($this->command.' "dvd"' ,$buf);
        $this->assertEquals('', ob_get_contents());
        while(ob_get_level() > 1){
            $this->assertEquals('', ob_get_clean());
        }
            
        
         $this->assertEquals('', implode($buf), print_r($buf, true));
    }
    
   function testIndexFailure2(){
        
        ob_start();
        exec($this->command.' "dvd" "somethingthatdoesnotexist"' ,$buf);
        $this->assertEquals('', ob_get_contents());
        while(ob_get_level() > 1){
            $this->assertEquals('', ob_get_clean());
        }
            
        
         $this->assertEquals('', implode($buf), print_r($buf, true));
    }
    
    
    function testIndexSuccess(){
        
        ob_start();
        exec($this->command.' "dvd" "everlight.dk"' ,$buf);
        $this->assertEquals('', ob_get_contents());
         while(ob_get_level() > 1){
            $this->assertEquals('', ob_get_clean());
        }
         $this->assertNotEquals('', implode($buf), print_r($buf, true));
    }
    
    function getSearch(){
        
    }
 
}