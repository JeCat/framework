<?php
namespace jc\auth ;

use jc\mvc\controller\IController;
use jc\lang\Exception;

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
		if( !IdManager::fromSession()->currentId() )
		{
			throw new AuthenticationException($aCauseController) ; 
		}
	}
	
	private $aCaseController ;
}

?>