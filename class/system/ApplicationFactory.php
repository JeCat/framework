<?php
namespace org\jecat\framework\system ;

use org\jecat\framework\fs\imp\LocalFileSystem;

use org\jecat\framework\setting\Setting;

use org\jecat\framework\fs\FileSystem;

use org\jecat\framework\setting\imp\FsSetting;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\locale\LocaleManager;

use org\jecat\framework\lang\oop\ClassLoader;
use org\jecat\framework\io\StdOutputFilterMgr;
use org\jecat\framework\io\PrintStream;
use org\jecat\framework\lang\Object ;

abstract class ApplicationFactory extends Object
{
	static public function singleton($bCreateNew=true)
	{
		$aInstance = Object::singleton(false,null,__CLASS__) ;
		
		if( !$aInstance )
		{
			$sFactoryClassName = empty($_SERVER['HTTP_HOST'])? 'CLAppFactory': 'HttpAppFactory' ;
			$sFactoryClassFullName = __NAMESPACE__.'\\'.$sFactoryClassName ;
			if( !class_exists($sFactoryClassFullName,false) )
			{
				require __DIR__.'/'.$sFactoryClassName.'.php' ;
			}
			
			$aInstance = new $sFactoryClassFullName() ;
			
			Object::setSingleton($aInstance,__CLASS__) ;
		}
		
		return $aInstance ;
	}

	public function create($sApplicationRootPath)
	{
		$aApp = new Application() ;
				
		$this->buildApplication($aApp,$sApplicationRootPath) ;
		
		return $aApp ;
	}
	
	/**
	 * 创建 Application 系统中的核心对象
	 * 如果没有 Application 单件对像，则将传入的 Application对像做为 Application 的全局单件
	 */
	public function buildApplication(Application $aApp,$sApplicationRootPath)
	{
		$aOriApp = Application::switchSingleton($aApp) ;		
		
		// filesystem
		FileSystem::setSingleton($this->createFileSystem($sApplicationRootPath)) ;
		
		// 初始化 class loader
		ClassLoader::setSingleton($this->createClassLoader()) ;
		
		// AccessRouter
		AccessRouter::setSingleton($this->createAccessRouter()) ;
		
		// LocalManager
		LocaleManager::setSingleton($this->createLocaleManager()) ;
		
		// Request
		Request::setSingleton( $this->createRequest() ) ;
		
		// Response
		Response::setSingleton( $this->createResponse() ) ;
		
		// setting
		Setting::setSingleton($this->createSetting()) ;
		
		// setting
		Setting::setSingleton($this->createSetting()) ;
		
		if($aOriApp)
		{
			Application::setSingleton($aOriApp) ;
		}
	}
	
	public function createFileSystem($sRootPath)
	{
		$aFileSystem = new LocalFileSystem($sRootPath) ;
		
		// 将 jc framework 挂载到 /framework 目录下
		$aFileSystem->mount( '/framework', LocalFileSystem::flyweight(\org\jecat\framework\PATH) ) ;
		
		return $aFileSystem ;
	}

	public function createClassLoader()
	{		
		$aClassLoader = new ClassLoader( FileSystem::singleton()->findFile("/classpath.php") ) ;
		$aClassLoader->addPackage( 'org\\jecat\\framework', '/framework/class' ) ; // 将 jecat 加入到 class loader 中
			
		return $aClassLoader ;
	}
	
	public function createAccessRouter()
	{
		return new AccessRouter() ;
	}
	
	public function createLocaleManager()
	{
		return new LocaleManager('cn') ;
	}
	
	abstract public function createRequest() ;

	
	public function createResponse(PrintStream $aPrinter)
	{
		$aRespn = new Response($aPrinter) ;
		$aRespn->setFilters(StdOutputFilterMgr::singleton()) ;
		
		return $aRespn ;
	}
	
	/**
	 * @return use org\jecat\framework\setting\Setting;
	 */
	public function createSetting()
	{
		if( !$aSettingFolder=FileSystem::singleton()->findFolder("/settings",FileSystem::FIND_AUTO_CREATE) )
		{
			throw new Exception("无法在目录 /setting 中建立系统配置") ;
		}

		return new FsSetting( $aSettingFolder ) ;

	}
	
	static private $aGlobalInstance ;
}

?>