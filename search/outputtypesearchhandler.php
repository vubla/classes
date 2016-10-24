<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of outputtypesearchhandler
 *
 * @author aba
 */
abstract class OutputTypeSearchHandler extends AnySearchObject {
    //put your code here
    abstract function getOutput($searcher,$logthis);
    
    function verifyoutput($out, $result, $searcher)
    {
        $ob = ob_get_contents();
        if(defined('DEBUG_SEARCH') && DEBUG_SEARCH){
            VublaLog::_n("Host ". $this->host);
            VublaLog::_n("wid". $this->wid);
            VublaLog::_n("Q ". $this->searcher->original);
            VublaLog::_n("Out" . $out);
            VublaLog::_n("Getting ob flush from search: \n" . nl2br($ob));
            VublaLog::_n("<pre>".print_r($this->searcher->errors, true)."</pre> \n");
            VublaLog::output();
            //exit;
        }
        else 
        {
            
            /*#############################
            #LOG AND TERMINATIION
            #############################*/
            if($searcher->errors){
                  throw new SearchException('Search contained errors');
            }
        
            if($ob){
                  throw new SearchException('Ob not empty '. print_r($ob, true));
            }

        }
    }
    
    function outputEncode($q){
        $from_encoding = Settings::get('encode_from', $this->wid);
        $vubla_encoding = Settings::getGlobal('vubla_encoding');
        if(!isset($_GET['enable']) && isset($from_encoding) && $from_encoding != $vubla_encoding){
            $q = iconv( $vubla_encoding, $from_encoding,$q);
        }
        return $q;
    }
}


