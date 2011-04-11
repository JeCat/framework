<?php

namespace jc\system ;

interface IApplicationFactory
{
	/**
	 * Enter description here ...
	 * 
	 * @return jc\system\Application
	 */
	public function application() ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return jc\system\Application
	 */
	public function createApplication() ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return jc\system\ClassLoader
	 */
	public function createClassLoader() ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return jc\system\AccessRouter
	 */
	public function createAccessRouter() ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return Request
	 */
	public function createRequest() ;

	/**
	 * Enter description here ...
	 * 
	 * @return jc\system\Response
	 */
	public function createResponse() ;
}
?>