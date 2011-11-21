<?php

namespace jc\db\sql ;

class Update extends MultiTableStatement implements IDataSettableStatement
{
	public function setReplace($bReplace=true)
	{
		$this->bReplace = $bReplace? true: false ;
	}
	
	public function isReplace()
	{
		return $this->bReplace ;
	}
	
	public function makeStatement(StatementState $aState)
	{
		$aState->setSupportLimitStart(false)
				->setSupportTableAlias(false) ;
				
		$this->checkValid(true) ;
		
		$sStatement = ($this->isReplace()? "REPLACE ": "UPDATE ")
				. $this->makeStatementTableList($aState)
				. " SET " ;

		$arrValues = array() ;
		foreach($this->mapData as $sClm=>&$sData)
		{
			$arrValues[] = $this->transColumn($sClm,$aState)." = ".$sData ;
		}
		
		$sStatement.= implode(", ", $arrValues) ;
		
		if( $aCriteria=$this->criteria(false) )
		{
			$sStatement.= ' ' . $aCriteria->makeStatement($aState) ;
		}
			
		return $sStatement ;
	}
	
	public function set($sColumnName,$statement)
	{
		$this->mapData[$sColumnName] = $statement ;
	}
	public function setData($sColumnName,$sData=null)
	{
		$this->mapData[$sColumnName] = $this->transValue($sData) ;
	}
	
	public function removeData($sColumnName)
	{
		unset($this->mapData[$sColumnName]) ;
	}

	public function clearData()
	{
		$this->mapData = array() ;
	}

	public function data($sColumnName)
	{
		return isset($this->mapData[$sColumnName])? $this->mapData[$sColumnName]: null ;
	}

	public function dataIterator()
	{
		return new \jc\pattern\iterate\ArrayIterator($this->mapData) ;
	}

	public function dataNameIterator()
	{
		return new \jc\pattern\iterate\ArrayIterator( array_keys($this->mapData) ) ;
	}
	
	private $mapData = array() ;
	
	private $bReplace = false ;
}

?>