<?php

require_once '../vublamailer.php';
require_once '../basedbtest.php';

$suite  = new PHPUnit_Framework_TestSuite("WordcorrectorTest");


class ProductTest extends BaseDbTest 
{
    public $data;
    
    private $test_max_prod;
    private $initial;
    
    function setUp() {
        //$this->buildDatabases();
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
       
        
    }
      
    
    function not_testSolrProduct()
    {
        echo('http://localhost:8983/solr/update?stream.body='.urlencode('
<add>
<doc>
  <field name="id">SOLR1000</field>
  <field name="name">Sfsfsdfafdasfse Search Server</field>
  <field name="manu">Apache Software Foundation</field>
  <field name="cat">software</field>
  <field name="cat">search</field>
  <field name="features">Advanced Full-Text Search Capabilities using Lucene</field>
  <field name="features">Optimized for High Volume Web Traffic</field>
  <field name="features">Standards Based Open Interfaces - XML and HTTP</field>
  <field name="features">Comprehensive HTML Administration Interfaces</field>
  <field name="features">Scalability - Efficient Replication to other Solr Search Servers</field>
  <field name="features">Flexible and Adaptable with XML configuration and Schema</field>
  <field name="features">Good unicode support: h&#xE9;llo (hello with an accent over the e)</field>
  <field name="price">0</field>
  <field name="popularity">10</field>
  <field name="inStock">true</field>
  <field name="incubationdate_dt">2006-01-17T00:00:00.000Z</field>
</doc>
</add>')); exit;
        /*  $options = getopt("f:");
        $infile = $options['f'];

        $url = "http://yoursolrserver:yoursolrport/yoursolrhome/update";
        $post_string = file_get_contents($infile);

        $header = array("Content-type:text/xml; charset=utf-8");

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

        $data = curl_exec($ch);

        if (curl_errno($ch)) {
           print "curl_error:" . curl_error($ch);
        } else {
           curl_close($ch);
           print "curl exited okay\n";
           echo "Data returned...\n";
           echo "------------------------------------\n";
           echo $data;
           echo "------------------------------------\n";
        }*/
        $object = new SolrProductXml();
        $object->setProduct($this->data);
        echo $object->getXml();
        $object->postToSolrInstance();
    }
}