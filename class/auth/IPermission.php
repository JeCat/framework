<?php
namespace org\jecat\framework\auth ;

interface IPermission
{
	public function check(IdManager $aIdManager) ;
	
	public function isNecessary() ;
	
	public function setNecessary($bNecessary=true) ;
}

?>