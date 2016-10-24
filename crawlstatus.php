<?php

class CrawlStatus 
{
    private $_vdo;
    private $wid;
    private $_data;
    
    public function __construct(int $wid)
    {
        $this->wid = $wid;
    } 
    
    private function _getVdo()
    {
        if(is_null($this->_vdo))
        {
            $this->_vdo = VPDO::getVdo(DB_METADATA);
        }
        return $this->_vdo;
    }
    
    private function _getData()
    {
        if(is_null($this->_data))
        {
            $sql = 'select status from crawllist where wid = ?';
            $this->_data = $this->_getVdo()->fetchOne($sql,array($this->wid));
        }
        return $this->_data;
    }
    
    private function _availableSteps()
    {
        return array('connecting','category','product');
    }
    
    public function steps()
    {
        $res = array();
        $isDone = true;
        foreach ($this->_availableSteps() as $value) 
        {
            $temp = new stdClass();
            $temp->name = $value;
            if($this->currentStep() != $value && $isDone)
            {
                $temp->status = 'Done';
            }
            else if($this->currentStep() == $value)
            {
                $temp->status = $this->currentStepProgress();
            }
            else 
            {
                $temp->status = 'Not Started';
            }
        }
    }
    
    public function currentStep()
    {
        $exploded = explode(':', $this->_getData());
        $step = $exploded[0];
        switch ($step) {
            case 'Started':
                return 'connecting';
                break;
            case 'category':
                return 'category';
                break;
            case 'product':
                return 'product';
                break;
            
            default:
                return 'error';
                break;
        }
    }
    
    public function currentStepProgress()
    {
        $exploded = explode(':', $this->_getData());
        if(count($exploded) <= 1)
        {
            return 0;
        }
        $progress = $exploded[1];
        return $progress;
    }
}
