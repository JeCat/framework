<?php
namespace org\jecat\framework\auth ;

interface IPermission
{
	public function check(IdManager $aIdManager) ;
}

?>