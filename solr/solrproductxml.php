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
class SolrProductXml {
    //put your code here
    
    /**
     *
     * @var DOMDocument 
     */
    private $doc;
    
    function getXml()
    {
        return $this->doc->saveXML();
        
    }
    function postToSolrInstance()
    {
        file_get_contents('http://localhost:8983/solr/update?stream.body=' . urlencode($this->getXml()));
        return $this;
    }
    function setProduct(Product $prod)
    {
     
        $this->doc = new DOMDocument('1.0');
        $this->doc->formatOutput = true;
        $root = $this->doc->createElement('add');
        $this->doc->appendChild($root);
        $productXml = $this->doc->createElement("doc");
        $root->appendChild($productXml);
        var_dump($prod->options);
        foreach($prod->options as $key=>$value)
        {
           $em = $this->doc->createElement("field"); 
           $em->setAttribute("name", $value['name']);
           var_dump($value);
           if(!isset($value['value']['name']))
           {
               continue;
            }
           $text = $this->doc->createTextNode($value['value']['name']);
           $em->appendChild($text);
           $productXml->appendChild($em);

        }
       
        return $this;
        // load
     /*    $arr = array();
         $doc = new DOMDocument();
         $doc->load('file.xml');
         $root = $doc->getElementsByTagName('root')->items[0];
         foreach($root->childNodes as $item) 
         { 
           $arr[$item->nodeName] = $item->nodeValue;
         }
        $prod->options
        */
    }
    
}

