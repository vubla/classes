<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of apioutputhandler
 *
 * @author aba
 */
class ApiOutputHandler extends OutputTypeSearchHandler {
    function getOutput($searcher,$logthis) {
        $dummyProductfactory = new DummyProductIdHandler($this->wid);
        $searcher->setProductFactory($dummyProductfactory);
        
        $searcherTimer = new IdHandleTimer($searcher);
        $result = $searcherTimer->getResults(array());
        
        if(!is_object($result))
        {
            throw new SearchException('Results was not an object');
        }

        if(!is_null($this->vubla_debug)){
            print_r($result); exit;
        }
        $result->total_search_time = $searcherTimer->getTime();
        if($logthis){
            
            $log = new SearchLog($this->wid,$result);
            $log->saveNew($this->vdo);
        }
        
        
        if(payment::getPackId($this->wid) == 1)
        {
            $result->display_logo = 1;
        }
        else 
        {
            $result->display_logo = 0;
        }
        
        $out = json_encode($result);
        
        if(!isset($_GET['on_vubla_site']))
        { 
          $out = $this->outputEncode($out);
        } 
        try {
            $this->verifyoutput($out, $result, $searcher);
        } catch(SearchException $e){
              throw $e;
        }
        VublaLog::killGently();  // We write to the log at many points and want it to send on kills, but not this time.
        return $out;
    }
}


