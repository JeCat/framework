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
	
	public function makeStatement($bFormat=false)
	{
		$this->checkValid(true) ;
		
		$aCriteria = $this->criteria() ;
		
		$sStatement = ($this->isReplace()? "REPLACE ": "UPDATE ")
				. $this->makeStatementTableList($bFormat)
				. " SET " ;

		$arrValues = array() ;
		foreach($this->mapData as $sClm=>$Data)
		{
			$arrValues[] = $this->transColumn($sClm)."=".$Data ;
		}
		
		$sStatement.= implode(", ", $arrValues) ;
		
		if( $aCriteria=$this->criteria(false) )
		{
			$sStatement.= ' ' . $aCriteria->makeStatement($bFormat) ;
		}
			
		return $sStatement ;
	}
	
	/*public function checkValid($bThrowException=true)
	{
		parent::checkValid($bThrowException) ;
	}*/
	
	public function set($sColumnName,$statement)
	{
		$this->mapData[$sColumnName] = $statement ;
	}
	public function setData($sColumnName,$sData=null)
	{
		$this->mapData[$sColumnName] = "'".addslashes($sData)."'" ;
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