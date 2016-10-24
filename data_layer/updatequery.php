<?php

class UpdateQuery extends AnyQueryObject
{
    private $tableName;
    private $vdo;
    private $update;
    private $wheres;
    private $toQuote;
    
    public function __construct($tableName,$vdo = null)
    {
        $this->tableName = $tableName;
        $this->vdo = $vdo;
        $this->update = array();
        $this->wheres = array();
    }
    
    public function set($columnName,$value,$toQuote = true)
    {
        $this->update[$columnName] = $value;
        $this->toQuote[$columnName] = $toQuote;
        return $this;
    }
    
    public function where($predicate)
    {
        if(is_string($predicate))
        {
            $predicate = new StringWhereClause($predicate);
        }
        $this->wheres[] = $predicate;
        return $this;
    }
    
    public function execute($vdo = null)
    {
        if(is_null($vdo)) $vdo = $this->vdo;
        if(is_null($vdo)) throw new MissingArgumentException('vdo argument not set in constructor and not set when execute was called.');
        
        $stm = $vdo->prepare($this->convertToSqlString($vdo));
        $result = $stm->execute();
        $num = $stm->rowCount();
        $stm->closeCursor();
        return $num;
    }
    
    public function convertToSqlString($vdo = null)
    {
        if(is_null($vdo)) $vdo = $this->vdo;
        if(is_null($vdo)) throw new MissingArgumentException('vdo argument not set in constructor and not set when execute was called.');
        if(empty($this->update)) throw new MissingArgumentException('$this->update was not set.');
        
        $sets = array();
        $wheres = array();
        foreach ($this->update as $key => $value) {
            if($this->toQuote[$key])
            {
                $sets[] = $key.'='.$vdo->quote($value);
            }
            else 
            {
                $sets[] = $key.'='.$value;
            }
        }
        foreach ($this->wheres as $value) {
            $wheres[] = $value->convertToSqlString($vdo);
        }
        $whereClause = '';
        if(!empty($wheres))
        {
            $whereClause = ' WHERE '.implode(' AND ', $wheres);
        }
        return 'UPDATE '.$this->tableName.' SET '.implode(', ', $sets) . $whereClause;
    }
}
