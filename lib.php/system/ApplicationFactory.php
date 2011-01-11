<?php

namespace jc\system ;

use jc\lang\Factory;

require_once dirname(__DIR__).'/lang/Factory.php' ;
require_once __DIR__.'/IApplicationFactory.php' ;

abstract class ApplicationFactory extends \jc\lang\Factory implements IApplicationFactory
{
	/**
	 * Enter description here ...
	 * 
	 * @return IApplicationFactory
	 */
	static public function createAppFactoryAuto()
	{
		$sClassName = empty($_SERVER['HTTP_HOST'])? 'CLAppFactory': 'HttpAppFactory' ;
		$sFullClassName = __NAMESPACE__.'\\'.$sClassName ;
		
		require_once __DIR__.'/'.$sClassName.'.php' ;
		return new $sFullClassName() ;
	}
	
	
	/**
	 * Enter description here ...
	 * 
	 * @return ClassLoader
	 */
	public function createClassLoader()
	{
		require_once __DIR__ . '/ClassLoader.php' ;
		
		$aClassLoader = $this->create(__NAMESPACE__.'\\ClassLoader') ;
		$aClassLoader->addPackage( realpath(__DIR__.'/..').'/', "jc" ) ; // 将 jcat 加入到 class loader 中
		
		return  $aClassLoader ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return AccessRouter
	 */
	public function createAccessRouter()
	{
		return $this->create(__NAMESPACE__.'\\AccessRouter') ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return Application
	 */
	public function application()
	{
		if( !$this->aApplication )
		{
			$this->aApplication = $this->createApplication() ;
		}
		
		return $this->aApplication ;
	}

	/**
	 * Enter description here ...
	 * 
	 * @return Application
	 */
	public function createApplication()
	{
		require_once __DIR__.'/Application.php' ;
		$aApp = $this->create(__NAMESPACE__."\\Application") ;
		
		// 初始化 class loader
		$aApp->setClassLoader($this->createClassLoader()) ;
		
		// 创建 Request/Response/AccessRouter 对象
		$aApp->setRequest( $this->createRequest() ) ;
		$aApp->setResponse( $this->createResponse() ) ;		
		$aApp->setAccessRouter( $this->createAccessRouter() ) ;
		
		return $aApp ;
	}
	
	private $aApplication ;
}
?>