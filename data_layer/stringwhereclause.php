<?

class StringWhereClause extends BaseWhereClause
{
    private $str;
    
    public function __construct($str)
    {
        if(!is_string($str)) throw new InavlidArgumentException('$str not a string');
        $this->str = $str;
    }
    
    public function convertToSqlString($vdo = null)
    {
        return $this->str;
    }
}
