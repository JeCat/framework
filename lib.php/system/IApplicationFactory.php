<?php

namespace jc\system ;

interface IApplicationFactory
{
	/**
	 * Enter description here ...
	 * 
	 * @return Application
	 */
	public function application() ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return Application
	 */
	public function createApplication() ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return ClassLoader
	 */
	public function createClassLoader() ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return AccessRouter
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
	 * @return Response
	 */
	public function createResponse() ;
}
?>