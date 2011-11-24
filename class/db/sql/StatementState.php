<?php
namespace org\jecat\framework\db\sql ;

class StatementState 
{
	public function supportTableAlias()
	{
		return $this->bSupportTableAlias ;
	}
	public function supportLimitStart()
	{
		return $this->bSupportLimitStart ;
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