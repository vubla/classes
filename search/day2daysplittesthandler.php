<?php

class Day2DaySplitTestHandler extends SplitTestHandler 
{
    public function __construct($wid) {
        parent::__construct($wid);
    }
    
    /**
     * @return bool Determine whether or not we should use vubla search engine besed on the split_test setting and user IP
     */
    public function useVubla($ip = null) 
    {
        if(Settings::get('split_test',$this->wid) != 1)
        {
            return true;
        }
        return (date('z'))%2;
    }
}
