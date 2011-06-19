<?php

namespace jc\db\sql ;

class Delete extends MultiTableStatement
{
	public function __construct($sTableName=null)
	{
		parent::__construct($sTableName,null) ;
	}
	
	public function makeStatement($bFormat=false)
	{
		$this->checkValid(true) ;
		
		$aCriteria = $this->criteria() ;
		
		$sStatement = "DELETE FROM " . $this->tables()->makeStatement($bFormat,false) 
				. ($aCriteria? (' WHERE '.$aCriteria->makeStatement($bFormat)): '') ;
		
		// limit
		$sStatement.= $this->makeStatementLimit($bFormat) ;
		
		return $sStatement ;
	}
}

?>