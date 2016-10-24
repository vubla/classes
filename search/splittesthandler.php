<?php

class SplitTestHandler extends AnySearchObject
{
	protected $wid;
	protected $vpdo;
	protected $validSearchEngines = array('Vubla','Native');
	
	public function __construct($wid) {
		$this->wid = $wid;
		$this->vpdo = VPDO::getVdo(DB_PREFIX.$wid);
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
		if(is_null($ip)) // use get param
		{
			$ip = $this->ip;
			if(is_null($ip)) 
			{
				throw new VublaException('No IP address provided');
			}
		}
		$ip = trim($ip);
		return $this->getSearchEngine($ip) == $this->vublaSearchEngine();
	}
	
	/**
	 * Get search engine for given ip address
	 * Sets it automatically if this ip is new
	 */
	protected function getSearchEngine($ip) 
	{
		$result = $this->getSearchEngineFromDB($ip);
		if(is_null($result)) {
			$result = $this->setSearchEngineToNext($ip);
		}
		return $result;
	}
	
	private function getSearchEngineFromDB($ip) 
	{
		$sql = 'SELECT search_engine FROM split_test WHERE ip = '.$this->vpdo->quote($ip);
		$result = $this->vpdo->fetchOne($sql);
		return $result;
	}
	
	private function setSearchEngineToNext($ip) 
	{
		$engine = $this->getNextSearchEngine();
		$this->setSearchEngineInDB($ip, $engine);
		return $engine;
	}
	
	private function getNextSearchEngine() 
	{
		$sql = 'SELECT search_engine FROM split_test ORDER BY id DESC LIMIT 1';
		$engine = $this->vpdo->fetchOne($sql);
		if(is_null($engine)) {
			return $this->vublaSearchEngine();
		}
		$pos = array_search($engine, $this->validSearchEngines());
		if($pos === false) {
			throw new VublaException('Invalid search engine detected: "'.$engine.'" Valid search engines are: '.implode(', ', $this->validSearchEngines()));
		}
		$temp = $this->validSearchEngines();
		return $temp[($pos+1)%sizeof($temp)];
	}
	
	private function setSearchEngineInDB($ip,$engine) 
	{
		if(array_search($engine, $this->validSearchEngines()) === FALSE) {
			throw new VublaException('Invalid search engine: "'.$engine.'" Valid search engines are: '.implode(', ', $this->validSearchEngines()));
		}
		$sql = 'INSERT INTO split_test(ip,search_engine) VALUES ('.$this->vpdo->quote($ip).','.$this->vpdo->quote($engine).')';
		$res = $this->vpdo->exec($sql);
		if($res != 1) {
			throw new VublaException('Failed to insert IP: "'.$ip.'" and search engine: '.$engine);
		}
	}
	
	private function validSearchEngines() {
		return $this->validSearchEngines;
	}
	
	private function vublaSearchEngine() {
		$temp = $this->validSearchEngines();
		return $temp[0];
	} 
}
