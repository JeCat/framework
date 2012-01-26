<?php
namespace org\jecat\framework\auth ;

class CallbackPermission extends PermissionBase
{
	public function __construct($callback)
	{
		$this->callback = $callback ;
	}
	
	public function check(IdManager $aIdManager)
	{
		return call_user_func_array($this->callback, array($aIdManager))!==false ;
	}
	
	private $callback ;
}

