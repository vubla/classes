<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';


$suite  = new PHPUnit_Framework_TestSuite("SolrDocumentFactoryTest");
//$result = PHPUnit_TextUI_TestRunner::run($suite);





class SolrDocumentFactoryTest extends BaseDbTest 
{
    public $data;
    
   
    function setUp() {
       $this->buildDatabases();
        $array = json_decode('{"categories":["4"],"pid":"1","wid":1,"options":[{"name":"products_model","value":{"name":"MG200MMS"}},{"name":"products_image","value":{"name":"images\/matrox\/mg200mms.gif"}},{"name":"products_price","value":{"name":"299.9900"}},{"name":"manufacturers_name","value":{"name":"Matrox"}},{"name":"manufacturers_id","value":{"name":"1"}},{"name":"url","value":{"name":"product_info.php?products_id=1"}},{"name":"products_name","value":{"name":"Matrox G200 MMS"}},{"name":"products_description","value":{"name":"Reinforcing its position as a multi-monitor trailblazer, Matrox Graphics Inc. has once again developed the most flexible and highly advanced solution in the industry. Introducing the new Matrox G200 Multi-Monitor Series; the first graphics card ever to support up to four DVI digital flat panel displays on a single 8\" PCI board.With continuing demand for digital flat panels in the financial workplace, the Matrox G200 MMS is the ultimate in flexible solutions. The Matrox G200 MMS also supports the new digital video interface (DVI) created by the Digital Display Working Group (DDWG) designed to ease the adoption of digital flat panels. Other configurations include composite video capture ability and onboard TV tuner, making the Matrox G200 MMS the complete solution for business needs.Based on the award-winning MGA-G200 graphics chip, the Matrox G200 Multi-Monitor Series provides superior 2D\/3D graphics acceleration to meet the demanding needs of business applications such as real-time stock quotes (Versus), live video feeds (Reuters & Bloombergs), multiple windows applications, word processing, spreadsheets and CAD."}},{"name":"buy_link","value":{"name":"index.php?cPath=3_10&sort=2a&action=buy_now&products_id=1"}},{"name":"Model","value":[{"products_attributes_id":"4","products_id":"1","options_id":"3","options_values_id":"5","value_price":"0.0000","price_prefix":"+","products_options_values_id":"5","language_id":"1","name":"Value"},{"products_attributes_id":"5","products_id":"1","options_id":"3","options_values_id":"6","value_price":"100.0000","price_prefix":"+","products_options_values_id":"6","language_id":"1","name":"Premium"}]},{"name":"Memory","value":[{"products_attributes_id":"1","products_id":"1","options_id":"4","options_values_id":"1","value_price":"0.0000","price_prefix":"+","products_options_values_id":"1","language_id":"1","name":"4 mb"},{"products_attributes_id":"2","products_id":"1","options_id":"4","options_values_id":"2","value_price":"50.0000","price_prefix":"+","products_options_values_id":"2","language_id":"1","name":"8 mb"},{"products_attributes_id":"3","products_id":"1","options_id":"4","options_values_id":"3","value_price":"70.0000","price_prefix":"+","products_options_values_id":"3","language_id":"1","name":"16 mb"}]},{"name":"pid","value":{"name":"1"}}]}',true);
        $className = 'Product';
        $this->data = unserialize(sprintf(
        'O:%d:"%s"%s',
        strlen($className),
        $className,
        strstr(serialize($array), ':')
        ));
        
      
    }
    
    function tearDown() {
     
       $this->dropDatabases();
       unset($this->data);
    }
    
    function testUpdateSolr()
    {
        $client = new SolrClient(array("hostname"=>"localhost","port"=>"8080", "path"=>"solr/webshop_10"));
        $factory = new SolrDocumentFactory(1);
        $doc = $factory->parseVublaProduct($this->data);

        $response = $client->addDocument($doc);
     
        $this->assertEquals(true, $response->success());
        
        
    }
    
   
} 






