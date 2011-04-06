<?php

namespace jc\db\sql ;

class Delete extends MultiTableStatement
{
	public function makeStatement($bFormat=false)
	{
		$this->checkValid(true) ;
		
		$aCriteria = $this->criteria() ;
		
		$sStatement = "DELETE" . $this->tables()->makeStatement($bFormat) 
				. ($aCriteria? $aCriteria->makeStatement($bFormat): '') ;
		
		return $sStatement ;
	}
}

?>