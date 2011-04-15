<?php

namespace jc\db\sql ;

class Update extends MultiTableStatement
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
		
		$sStatement = ($this->isReplace()? "REPLACE": "UPDATE")
				. $this->tables()->makeStatement($bFormat) 
				. " SET " ;

		$arrValues = array() ;
		foreach($this->mapData as $sClm=>$Data)
		{
			$arrValues[] = $sClm."=".addslashes($Data) ;
		}
		
		$sStatement.= implode(", ", $arrValues) ;
		
		if( $aCriteria )
		{
			$sStatement.= $aCriteria->makeStatement($bFormat) ;
		}
		
		return $sStatement ;
	}
	
	/*public function checkValid($bThrowException=true)
	{
		parent::checkValid($bThrowException) ;
	}*/

	public function setData($sColumnName,$sData=null)
	{
		$this->mapData[$sColumnName] = $sData ;
	}
	
	public function removeData($sColumnName)
	{
		unset($this->mapData[$sColumnName]) ;
	}
	
	private $mapData = array() ;
	
	private $bReplace = false ;
}

?>