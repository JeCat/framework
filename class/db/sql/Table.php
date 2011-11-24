<?php
namespace org\jecat\framework\db\sql ;

class Table extends SubStatement
{
	public function __construct($sTableName,$sAlias=null)
	{
		$this->sTableName = $sTableName ;
		$this->sAlias = $sAlias?:$sTableName ;
	}
	
	public function makeStatement(StatementState $aState)
	{
		$sSql = "`$this->sTableName`" ;
		if( $this->sAlias and $aState->supportTableAlias() and $this->sAlias!=$this->sTableName )
		{
			$sSql.= " AS `{$this->sAlias}`" ;
		}
		
		foreach($this->arrJoinSubStatements as $aJoin)
		{
			$sSql.= $aJoin->makeStatement($aState) ;
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
	
	public function clearJoins()
	{
		$this->arrJoinSubStatements = array() ;
	}
	
	public function joins()
	{
		return $this->arrJoinSubStatements ;
	}
	
	private $sTableName = '' ;
	private $sAlias = '' ;
	private $arrJoinSubStatements = array() ;
}

?>
