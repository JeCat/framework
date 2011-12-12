<?php
namespace org\jecat\framework\auth ;

use org\jecat\framework\mvc\controller\IController;
use org\jecat\framework\lang\Exception;

class AuthenticationException extends Exception
{
	public function __construct(IController $aCauseController,$sMessage=null,$aArgvs=array())
	{
		$this->aCaseController = $aCauseController ;
		
		if(!$sMessage)
		{
			$sMessage = "访问权限被拒绝" ;
		}
		parent::__construct($sMessage,$aArgvs) ;
	}
	
	public function caseController()
	{
		return $this->aCaseController ;
	}
	
	static public function checkLogined(IController $aCauseController)
	{
		if( !IdManager::singleton()->currentId() )
		{
			throw new AuthenticationException($aCauseController) ; 
		}
	}
	
	private $aCaseController ;
}

?>