<?php
namespace org\jecat\framework\db\sql2 ;

class Table extends SQL
{
	public function __construct()
	{
	}
	
	public function __toString()
	{}
	
	public function name()
	{
		return $this->arrRowSql['table'] ;
	}
	
	public function setName($sTableName)
	{
		$this->arrRowSql['table'] = $sTableName ;
		return $this ;
	}
	
	public function alias($bDontUseTablename=false)
	{
		return isset($this->arrRowSql['alias'])?
					$this->arrRowSql['alias'] :
					( $bDontUseTablename? null: $this->arrRowSql['table'] ) ;
	}
	
	public function setAlias($sAlias)
	{
		$this->arrRowSql['alias'] = $sAlias ;
		return $this ;
	}
	
}

?>
