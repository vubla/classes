<?php

class BenchmarkStatisticsProvider extends StatisticsProvider {


    function getAllBenchmarkResults(){

        $db = new Vpdo('phpunit_main', 'phpunit', 'Trekant01', null, 'localhost');
        return $db->getRow('select avg(`option_filter_time`), avg(`sorting_time`), avg(`widget_factory_time`), avg(`string_filter_time`), avg(`product_factory_time`), avg(`total_search_time`), avg(`total`) from (SELECT * FROM `benchmarks` order by id desc limit 11) as a
        ');
        
        
    }
    
    function saveAllStatsToFile($name){
        
        $fh = fopen($name, 'r+');
        $stats = $this->getAllBenchmarkResults();
        foreach ($stats as $stat) {
              fwrite($fh, $stat . PHP_EOL );
        }
      
    }
}