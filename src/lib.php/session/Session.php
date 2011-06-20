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
}

?>