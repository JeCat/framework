<?php
namespace jc\session ;

use jc\lang\Object;

abstract class Session extends Object implements ISession
{	
	public function cookieParam()
	{
		return session_name() ;
	}
	
	public function setCookieParam($sParamName) 
	{
		session_name($sParamName) ;
	}
	
	/**
	 * @return bool
	 */
	public function start()
	{
		if( $this->bSessionStarted )
		{
			return true ;
		}
		
		if(session_start())
		{
			$this->bSessionStarted = true ;
			return true ;
		}
		else 
		{
			return false ;
		}
	}
	
	/**
	 * @return bool
	 */
	public function hasStarted()
	{
		return $this->bSessionStarted
	}
	
	static public function singleton ($bCreateNew=true,$createArgvs=null,$bAutoStart=true)
	{
		$aInstance = parent::singleton ($bCreateNew,$createArgvs) ;
		
		if( $bAutoStart and !$aInstance->hasStarted() )
		{
			$aInstance->start() ;
		}
		
		return $aInstance ;
	}
	
	private $bSessionStarted = false ;
}

?>