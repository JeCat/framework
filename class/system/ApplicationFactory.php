<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.7.1
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\system ;

use org\jecat\framework\fs\Folder;
use org\jecat\framework\lang\oop\ShadowClassPackage;
use org\jecat\framework\mvc\controller\Response;
use org\jecat\framework\mvc\model\db\orm\Prototype;
use org\jecat\framework\setting\Setting;
use org\jecat\framework\setting\imp\FsSetting;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\locale\LocaleManager;
use org\jecat\framework\lang\oop\ClassLoader;
use org\jecat\framework\io\StdOutputFilterMgr;
use org\jecat\framework\lang\Object;

abstract class ApplicationFactory extends Object
{
	static public function singleton($bCreateNew=true,$createArgvs=null,$sClass=null)
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
		Folder::setSingleton(new Folder($sApplicationRootPath)) ;
		
		// 初始化 class loader
		ClassLoader::setSingleton($this->createClassLoader($aApp)) ;
		
		// AccessRouter
		AccessRouter::setSingleton($this->createAccessRouter($aApp)) ;
		
		// LocalManager
		LocaleManager::setSingleton($this->createLocaleManager($aApp)) ;
		
		// Request
		Request::setSingleton( $this->createRequest($aApp) ) ;
		
		// setting
		Setting::setSingleton($this->createSetting($aApp)) ;
		
		if($aOriApp)
		{
			Application::setSingleton($aOriApp) ;
		}
	}

	public function createClassLoader()
	{		
		$aClassLoader = new ClassLoader() ;
		$aFolder = Folder::singleton() ;
		
		// 将 jecat 加入到 class loader 中
		$aClassLoader->addPackage( 'org\\jecat\\framework', \org\jecat\framework\CLASSPATH ) ;
		
		// 将保存 数据表 实现类的临时目录加入到 class loader 中
		$aPackage = new ShadowClassPackage(
				Prototype::MODEL_IMPLEMENT_CLASS_BASE
				, Prototype::MODEL_IMPLEMENT_CLASS_NS
				, $aFolder->findFolder('/data/class/db/model',Folder::FIND_AUTO_CREATE)
		) ;
		$aClassLoader->addPackage( $aPackage ) ;
		
		// 将保存 数据表原型 实现类的临时目录加入到 class loader 中
		$aPackage = new ShadowClassPackage(
				Prototype::PROTOTYPE_IMPLEMENT_CLASS_BASE
				, Prototype::PROTOTYPE_IMPLEMENT_CLASS_NS
				, $aFolder->findFolder('/data/class/db/prototype',Folder::FIND_AUTO_CREATE)
		) ;
		$aClassLoader->addPackage( $aPackage ) ;

		return $aClassLoader ;
	}
	
	public function createAccessRouter(Application $aApp)
	{
		return new AccessRouter() ;
	}
	
	public function createLocaleManager(Application $aApp)
	{
		return new LocaleManager('cn') ;
	}
	
	abstract public function createRequest(Application $aApp) ;

	
	public function createResponse(Application $aApp)
	{
		$aRespn = new Response($this->createResponseDevice()) ;
		$aRespn->setFilters(StdOutputFilterMgr::singleton()) ;
		
		return $aRespn ;
	}
	
	abstract public function createResponseDevice() ;
	
	/**
	 * @return use org\jecat\framework\setting\Setting;
	 */
	public function createSetting()
	{
		if( !$aSettingFolder=Folder::singleton()->findFolder("/settings",Folder::FIND_AUTO_CREATE) )
		{
			throw new Exception("无法在目录 /setting 中建立系统配置") ;
		}

		return new FsSetting( $aSettingFolder ) ;
	}
	
	static private $aGlobalInstance ;
}

