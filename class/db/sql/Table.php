<?php
namespace org\jecat\framework\db\sql ;

class Table extends SQL
{
	public function __construct()
	{
	}
	
	public function __toString()
	{}
	
	public function name()
	{
		return $this->arrRawSql['table'] ;
	}
	
	public function setName($sTableName)
	{
		$this->arrRawSql['table'] = $sTableName ;
		return $this ;
	}
	
	public function alias($bDontUseTablename=false)
	{
		return isset($this->arrRawSql['alias'])?
					$this->arrRawSql['alias'] :
					( $bDontUseTablename? null: $this->arrRawSql['table'] ) ;
	}
	
	public function setAlias($sAlias)
	{
		$this->arrRawSql['alias'] = $sAlias ;
		return $this ;
	}
	
}

?>
