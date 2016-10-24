<?php

class IdHandleTimer  {
    
    var $object;
    var $start;
    var $total;
    var $stopped;
    var $running;
    function __construct(ProductIdHandler $object)
    {
        $this->object = $object;
    }  
    
    function getResults(array $pids)
    {
      $this->startTimer();
      $ret = $this->object->getResults($pids);   
      $this->stopTimer();
      return $ret;
    }
    
    function startTimer()
    {
        $this->running = true;
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $this->start = $time;   
    }
    
    function stopTimer()
    {
        if(!$this->running){
            throw new Exception("Timer was never started", 1);  
        }
        $this->running = false;
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $this->stopped = $time;
        $this->total = round(($this->stopped  - $this->start), 4);
    }
    
    function getTime()
    {
        return $this->total;
    }

}
