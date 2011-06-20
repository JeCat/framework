<?php
namespace jc\session ;

interface ISession 
{
	public function sessionId() ;
	
	public function setSessionId($sId) ;
	
	public function & variable($sName) ;

	public function addVariable($sName,& $var) ;
	
	public function hasVariable($sName) ;

	public function removeVariable($sName) ;
	
	public function clear() ;
	
	public function cookieParam() ;
	
	public function setCookieParam() ;
	
	/**
	 * @return bool
	 */
	public function start() ;
	
	/**
	 * 将session中的数据保存到实际设备中
	 */
	public function commit() ;
	
	/**
	 * @return \Iterator
	 */
	public function variableNameIterator() ;
	
}

?>