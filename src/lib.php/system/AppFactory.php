<?php
namespace jc\system ;

use jc\io\PrintStream;
use jc\lang\Object ;

abstract class AppFactory extends Object
{
	static public function createFactory()
	{
		$sFactoryClassName = empty($_SERVER['HTTP_HOST'])? 'CLAppFactory': 'HttpAppFactory' ;
		$sFactoryClassFullName = __NAMESPACE__.'\\'.$sFactoryClassName ;
		if( !class_exists($sFactoryClassFullName,false) )
		{
			require __DIR__.'/'.$sFactoryClassName.'.php' ;
		}
		return new $sFactoryClassFullName() ;
	}

	public function create()
	{
		$aApp = new Application() ;
		
		// 初始化 class loader
		$aApp->setClassLoader(
			$this->createClassLoader($aApp)
		) ;
		
		// 创建 AccessRouter 对象
		$aApp->AccessRouter(
			$this->createAccessRouter($aApp)
		) ;
		
		// 创建 LocaleManager 对象
		$aApp->setLocaleManager(
			$this->createLocaleManager($aApp)
		) ;
		
		// 创建 Request/Response
		$aApp->setRequest(
			$this->createRequest($aApp)
		) ;
		$aApp->setResponse(
			$this->createResponse($aApp)
		) ;
		
		return $aApp ;
	}

	public function createClassLoader(CoreApplication $aApp)
	{
		$aClassLoader = new ClassLoader() ;
		$aClassLoader->setApplication($aApp) ;
		$aClassLoader->addPackage( realpath(__DIR__.'/..').'/', "jc" ) ; // 将 jcat 加入到 class loader 中
		
		return $aClassLoader ;		
	}

	public function createAccessRouter(CoreApplication $aApp)
	{
		return $aApp->create('AccessRouter',__NAMESPACE__) ;
	}
	
	public function createLocaleManager(CoreApplication $aApp)
	{
		return $aApp->create( 'LocaleManager','jc\locale',array('cn') ) ;
	}
	
	abstract public function createRequest(CoreApplication $aApp) ;

	
	public function createResponse(CoreApplication $aApp,PrintStream $aPrinter)
	{
		$aRespn = $aApp->create( 'Response', __NAMESPACE__, array($aPrinter) ) ;
		$aRespn->setFilters($aApp->create( 'StdOutputFilterMgr', 'jc\io' ) ) ;
		
		return $aRespn ;
	}
}

?>