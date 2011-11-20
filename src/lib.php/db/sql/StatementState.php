<?php
namespace jc\db\sql ;

class StatementState 
{
	public function supportTableAlias()
	{
		$this->bSupportTableAlias = true ;
	}
	public function supportLimitStart()
	{
		$this->bSupportLimitStart = true ;
	}
	

	public function setSupportTableAlias($bSupportTableAlias=true)
	{
		$this->bSupportTableAlias = $bSupportTableAlias? true: false ;
		return $this ;
	}
	public function setSupportLimitStart($bSupportLimitStart=true)
	{
		$this->bSupportLimitStart = $bSupportLimitStart? true: false ;
		return $this ;
	}
	
	public $bSupportTableAlias = false ;
	public $bSupportLimitStart = false ;
}

?>