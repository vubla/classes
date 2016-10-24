<?php
   
require '../vublamailer.php';
require '../basedbtest.php';
require '../../../login/controllers/configurationController.php';
$_SESSION['uid'] = 1;  
  
class TestConfigurationController extends ConfigurationController {
    
    function skip(){
        throw new exception("Skip called");
    }
    function next(){
        throw new exception("next called");
    }   
    function previous(){
        throw new exception("previous called");
    }
    function getWid(){
        return 1;
        
    }
}
   
class ConfigurationControllerTest extends BaseDbTest 
{
    function setUp() 
    {
        $this->c = new TestConfigurationController();
        $this->buildDatabases(); 
      
 

    }
       

    function tearDown() 
    {
      
        $this->dropDatabases();
          unset($this->c);
    }
    
   
   
    
    function testTemplateStep()
    {
        
        $this->c->templateStep();
        
        $this->assertEquals('templatestep', $this->c->vars->stepname);
        $this->assertInternalType('array',$this->c->vars->templates);
        
    }



        
 

}


