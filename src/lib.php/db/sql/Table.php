<?php
namespace jc\db\sql ;

class Table extends SubStatement
{
	public function __construct($sTableName,$sAlias=null)
	{
		$this->sTableName = $sTableName ;
		$this->sAlias = $sAlias?:$sTableName ;
	}
	
	public function makeStatement($bFormat=false)
	{
		$sSql = "`$this->sTableName`" ;
		if( $this->sAlias and $this->sAlias!=$this->sTableName )
		{
			$sSql.= " AS `{$this->sAlias}`" ;
		}
		
		foreach($this->arrJoinSubStatements as $aJoin)
		{
			$sSql.= $aJoin->makeStatement() ;
		}
		
		return $sSql ;
	}
	
	public function checkValid($bThrowException=true) 
	{
		return true ;
	}
	
	public function name()
	{
		return $this->sTableName ;
	}
	
	public function setName($sTableName)
	{
		$this->sTableName = $sTableName ;
	}
	
	public function alias()
	{
		return $this->sAlias ;
	}
	
	public function setAlias($sAlias)
	{
		$this->sAlias = $sAlias ;
	}
	
	public function addJoin(TablesJoin $aJoin)
	{
		$this->arrJoinSubStatements[] = $aJoin ;
	}
	
	private $sTableName = '' ;
	private $sAlias = '' ;
	private $arrJoinSubStatements = array() ;
}

?>