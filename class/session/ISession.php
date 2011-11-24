<?php
namespace org\jecat\framework\session ;

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
	
	public function setCookieParam($sParamName) ;
	
	/**
	 * @return bool
	 */
	public function start() ;
	
	/**
	 * @return bool
	 */
	public function hasStarted() ;
	
	/**
	 * 将session中的数据保存到实际设备中
	 */
	public function commit() ;
	
	/**
	 * @return org\jecat\framework\pattern\iterate\INonlinearIterator
	 */
	public function variableNameIterator() ;
	
}

?>