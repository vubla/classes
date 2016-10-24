<?php


class SanityChecker {
        private $wid;
        function __construct($wid)
        {
            $this->wid = $wid;
            $this->mdb = vpdo::getVdo(DB_METADATA);
            $this->wdb = vpdo::getVdo(DB_PREFIX.$wid);
        }
    
        function getFoulProducts(){
            $foulProducts = $this->wdb->getTableList("SELECT * FROM  `product_options` WHERE  `discount_price` IS NOT NULL AND  `discount_price` !=  `lowest_price` ");
            return $foulProducts;
        }
    
        function generateReport()
        {
            $everythingIsFine = true;
            $report = "<h1>Sanity Report for Webshop:".$this->wid."</h1>";
            
            $foulProducts = $this->getFoulProducts();
            //$words = $this->getWords();
            if(!empty($foulProducts))
            {
                $everythingIsFine = false;
                $report .= "<h2>Foul Products</h2>";
                $report .= "This shop has <b>". sizeof($foulProducts) . "</b> foul products. <br /> It currently only checks if lowest price is greater than discount price.";
                $report .= "<table border=1>";
                $report .= "<tr>";
                foreach($foulProducts[0] as $key=>$val)
                {
                    $report .= "<th style=\"border:1px solid black;\">".$key."</th>";
                }  
                  
                $report .= "</tr>";    
                
                 foreach($foulProducts as $key=>$val)
                {
                   $report .= "<tr>";
                   foreach($val as $value) {
                       $report .= "<td style=\"border:1px solid black;\">";  
                       $report .= $value;
                       $report .= "</td>";  
                   }
                   $report .= "</tr>";  
                }   
                      
                $report .= "</table>";
            }

            if($everythingIsFine) {
                
                $report .= "<h2>Everything is fine</h2>";
            }   
            return $report; 
        }
    
}
