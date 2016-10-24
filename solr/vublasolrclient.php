<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of vublasolrclient
 *
 * @author rasmus
 */
class VublaSolrClient extends SolrBase {

    private $solrDocFactory;
    private $solrClient;
    private $solrHosts  = array(array("host"=>"solr1.vubla.com", "port"=>"8080"));
    
    function __construct($wid){
         $this->wid = $wid;
         $this->init();
    }
    
    private function init()
    {
        $this->create();
    }
    
    private function create()
    {
        $webshop_name = $this->getName();
        foreach($this->solrHosts as $solrHost){
            echo $cmd = 'http://'.$solrHost['host'].':'.$solrHost['port'].'/solr/admin/cores?action=CREATE&name='.$webshop_name.'&instanceDir=/opt/solr/instances/&dataDir=/opt/solr/data/'.$webshop_name.'&collection='.$webshop_name.'&collection.configName='.Settings::get('solr_config_name',$this->getWid());
      //      echo 'http://'.$solrHost['host'].':'.$solrHost['port'].'/solr/admin/cores?action=CREATE&name='.$webshop_name.'&dataDir=/etc/solr/data/'.$webshop_name.'collection='.$webshop_name.'&collection.configName='.;
            file_get_contents($cmd);
        }
    }
    
     private function runCommand($command)
    {
        $webshop_name = $this->getName();
        foreach($this->solrHosts as $solrHost){
            file_get_contents('http://'.$solrHost['host'].':'.$solrHost['port'].'/solr/'.$webshop_name.'/'.$command);
        }
    }
    
    private function getName()
    {
        return "webshop_".$this->getWid();
    }    
    
    function getWid(){
        return $this->wid;
    }
    
    function commit()
    {
        $this->runCommand('update?commit=true');
    }
}

?>
