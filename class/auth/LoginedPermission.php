<?php
namespace org\jecat\framework\auth ;

use org\jecat\framework\lang\Object;

class LoginedPermission extends Object implements IPermission
{
	public function check(IdManager $aIdManager)
	{
		return $aIdManager->currentId()? true: false ;
	}
}

