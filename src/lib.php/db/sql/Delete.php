<?php

namespace jc\db\sql ;

class Delete extends MultiTableStatement
{
	public function __construct($sTableName=null)
	{
		parent::__construct($sTableName,null) ;
	}
	
	public function makeStatement(StatementState $aState)
	{
		$aState->setSupportLimitStart(false)
				->setSupportTableAlias(false) ;
				
		$this->checkValid(true) ;
		
		$aCriteria = $this->criteria() ;
		
		$sStatement = "DELETE FROM " . $this->makeStatementTableList($aState) ;
	
		if($aaCriteria=$this->criteria(false))
		{
			$sStatement.= $aaCriteria->makeStatement($aState) ;
		}
		// limit
//		$sStatement.= $this->makeStatementLimit($aState) ;
		
		return $sStatement ;
	}
}

?>