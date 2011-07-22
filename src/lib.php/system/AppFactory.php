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
		return $this->build(new Application()) ;
	}
	
	public function build(CoreApplication $aApp)
	{
		// 初始化 class loader
		$aApp->setClassLoader(
			$this->createClassLoader($aApp)
		) ;
		
		// 创建 AccessRouter 对象
		$aApp->setAccessRouter(
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
		$aClassLoader->addPackage( 'jc', dirname(dirname(dirname(__DIR__))).'/bin/lib.php', dirname(__DIR__) ) ; // 将 jcat 加入到 class loader 中
		
		return $aClassLoader ;		
	}

	public function createAccessRouter(CoreApplication $aApp)
	{
		$aAccessRouter = new AccessRouter('cn') ;
		$aAccessRouter->setApplication($aApp) ;
		return $aAccessRouter ;
	}
	
	public function createLocaleManager(CoreApplication $aApp)
	{
		$aLocal = new \jc\locale\LocaleManager('cn') ;
		$aLocal->setApplication($aApp) ;
		return $aLocal ;
	}
	
	abstract public function createRequest(CoreApplication $aApp) ;

	
	public function createResponse(CoreApplication $aApp,PrintStream $aPrinter)
	{
		$aFilter = new \jc\io\StdOutputFilterMgr() ;
		$aFilter->setApplication($aApp) ;
		
		$aRespn = new Response($aPrinter) ;
		$aRespn->setApplication($aApp) ;
		$aRespn->setFilters($aFilter) ;
		
		return $aRespn ;
	}
}

?>