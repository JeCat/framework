<?php
namespace jc\session ;


use jc\lang\Exception;

class OriginalSession extends Session
{
	public function __construct()
	{
		if( self::singleton(false) )
		{
			throw new Exception("OriginalSession 类只能在单件模式下工作，OriginalSession类已经创建，无法重复创建该类的实例。") ;
		}
		
		self::setSingleton($this) ;
	}
	
	public function sessionId()
	{
		return session_id() ;
	}
	
	public function setSessionId($sId)
	{
		session_id($sId) ;
	}
	
	public function & variable($sName)
	{
		return $_SESSION[$sName] ;
	}

	public function addVariable($sName,& $var)
	{
		$_SESSION[$sName] =& $var ;
	}
	
	public function hasVariable($sName)
	{
		return array_key_exists($sName, $_SESSION) ;
	}

	public function removeVariable($sName)
	{
		unset($_SESSION[$sName]) ;
	}
	
	public function clear()
	{
		session_unset() ;
	}
	
	/**
	 * @return jc\pattern\iterate\INonlinearIterator
	 */
	public function variableNameIterator()
	{
		return new \jc\pattern\iterate\ArrayIterator( array_keys($_SESSION) ) ;
	}
	
	/**
	 * 将session中的数据保存到实际设备中
	 */
	public function commit()
	{
		session_commit() ;
	}
}

?>