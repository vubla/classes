<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of solrtransformer
 *
 * @author rasmus
 */
class SolrSchema {
    //put your code here
    
    /**
     *
     * @var DOMDocument 
     */
    private $doc;
    
    private $optionsSettings;
    
    function getXml()
    {
        return $this->doc->saveXML();
        
    }
    
    function __construct()
    {
         $this->optionsSettings = new OptionsSettingSet(10);
         $this->optionsSettings->fillFromDb();
    }
    function _generateSchema()
    {
       
    }
   
    
}

