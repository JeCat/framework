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
namespace org\jecat\framework\lang\aop ;

use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\fs\File;
use org\jecat\framework\lang\compile\IStrategySummary;
use org\jecat\framework\fs\FSO;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\oop\Package;
use org\jecat\framework\lang\compile\CompilerFactory;
use org\jecat\framework\lang\oop\ClassLoader;
use org\jecat\framework\pattern\composite\Container;
use org\jecat\framework\lang\Object;

class AOP extends Object implements IStrategySummary, \Serializable
{
	/**
	 * 注册一个 Aspect 类
	 */
	public function register($sAspectClass)
	{
		trigger_error('请使用 AOP::registerBean() 替代 AOP::register()',E_USER_DEPRECATED ) ;
		
		if( !isset($this->arrAspectClasses[$sAspectClass]) )
		{
			$this->arrAspectClasses[$sAspectClass] = $sAspectClass ;
			$this->parseAspectClass($sAspectClass) ;
			$this->aPointcutIterator = null ;
			$this->aJointPointIterator = null ;
		}

		return $this ;
	}
	
	/**
	 * @return AOP 
	 */
	public function registerBean(array $arrConfig,$sAspectDefineFile=null)
	{
		if( empty($arrConfig['class']) )
		{
			$arrConfig['class'] = 'aspect' ;
		}
		
		$aAspect = BeanFactory::singleton()->createBean($arrConfig) ;
		if($sAspectDefineFile)
		{
			$aAspect->setAspectFilepath(FSO::tidyPath($sAspectDefineFile)) ;
		}
		$this->aspects()->add($aAspect) ;
		
		$this->aPointcutIterator = null ;
		$this->aJointPointIterator = null ;
		
		return $this ;
	}
	
	public function unregister(Aspect $aAspect)
	{
		unset( $this->arrAspectClasses[ $aAspect->aspectName() ] ) ;
		$this->aspects()->remove($aAspect) ;
	}
	
	/**
	 * @return \Iterator
	 */
	public function aspectIterator() 
	{		
		return $this->aAspects? $this->aspects()->iterator(): new \EmptyIterator() ;
	}
	
	/**
	 * @return \Iterator
	 */
	public function jointPointIterator() 
	{
		if(!$this->aJointPointIterator)
		{
			if( !$this->aAspects )
			{
				$this->aJointPointIterator = new \EmptyIterator() ;
			}
			else
			{
				$this->aJointPointIterator = new \AppendIterator() ;
				foreach($this->aspects()->iterator() as $aAspects)
				{
					foreach ($aAspects->pointcuts()->iterator() as $aPointcut)
					{
						$this->aJointPointIterator->append($aPointcut->jointPoints()->iterator()) ;
					}
				}
			}
		}
		return $this->aJointPointIterator ;
	}
	
	/**
	 * @return \Iterator
	 */
	public function pointcutIterator() 
	{		
		if(!$this->aPointcutIterator)
		{
			if( !$this->aAspects )
			{
				$this->aPointcutIterator = new \EmptyIterator() ;
			}
			else
			{
				$this->aPointcutIterator = new \AppendIterator() ;
				foreach($this->aspects()->iterator() as $aAspects)
				{
					$this->aPointcutIterator->append($aAspects->pointcuts()->iterator()) ;
				}
			}
		}
		return $this->aPointcutIterator;
	}
		
	public function weave()
	{
		$aClassLoader = ClassLoader::singleton() ;
		$arrBeWeavedClasses = array() ;
		$aCompiler = null ;
		
		foreach($this->jointPointIterator() as $aJointPoint)
		{
			$sBeWeavedClass = $aJointPoint->weaveClass() ;
			if( !in_array($sBeWeavedClass,$arrBeWeavedClasses) )
			{				
				$sSrcClassFile = $aClassLoader->searchClass($sBeWeavedClass,Package::nocompiled) ;
				$sCmpdClassFile = $aClassLoader->searchClass($sBeWeavedClass,Package::compiled) ;
				
				if( !$sSrcClassFile )
				{
					throw new Exception(
						"AOP 无法将目标代码织入到 JointPoint %s 中：没有找到类 %s 的源文件。"
						, array($aJointPoint->$sBeWeavedClass,$aJointPoint->name())
					) ;
				}
				
				if( !$sCmpdClassFile )
				{
					throw new Exception(
						"AOP 无法将目标代码织入到 JointPoint %s 中：没有找到类 %s 的编译文件。"
						, array($aJointPoint->$sBeWeavedClass,$aJointPoint->name())
					) ;
				}
			
				if(!$aCompiler)
				{
					$aCompiler = $this->createClassCompiler() ;
				}
				
				$aCompiler->compile( File::createInstance($sSrcClassFile)->openReader(), File::createInstance($sCmpdClassFile)->openWriter() ) ;
				
				$arrBeWeavedClasses[] = $sBeWeavedClass ;
			}
		}
	}
	
	/**
	 * aspects库 的指纹签名
	 */
	public function strategySummary()
	{
		if( $this->sAspectLibSignture )
		{
			return $this->sAspectLibSignture ;
		}
		
		if(!$this->arrAspectClasses)
		{
			return '' ;
		}
		
		// 根据注册的 apect 类生成签名
		$arrBox = null ;
		$aClassLoader = ClassLoader::singleton() ;
		foreach($this->arrAspectClasses as $sAspectClass)
		{
			$arrBox[] = $sAspectClass ;
		}
		
		return $this->sAspectLibSignture = md5( serialize($arrBox) ) ;
	}
	
	public function createClassCompiler()
	{
		$aCompiler = CompilerFactory::createInstance()->create() ;
		$aCompiler->registerGenerator("org\\jecat\\framework\\lang\\compile\\object\\FunctionDefine","org\\jecat\\framework\\lang\\aop\\compiler\\FunctionDefineGenerator") ;
		
		return $aCompiler ;
	}
	
	
	private function parseAspectClass($sAspectClass)
	{
		if( !$sClassFile = ClassLoader::singleton()->searchClass($sAspectClass,Package::nocompiled) )
		{
			throw new Exception("注册到AOP中的Aspace(%s)不存在; Aspace必须是一个有效的类",$sAspectClass) ;
		}
	
		$aClassCompiler = CompilerFactory::singleton()->create() ;
		$aTokenPool = $aClassCompiler->scan($sClassFile) ;
		$aClassCompiler->interpret($aTokenPool) ;
	
		if( !$aClassToken=$aTokenPool->findClass($sAspectClass) )
		{
			throw new Exception("根据 class path 搜索到class的定义文件：%s，但是该文件中没有定义：%s",
					array($sAspectClass,$sClassFile,$sAspectClass)
			) ;
		}
	
		$this->aspects()->add(Aspect::createFromToken($aClassToken,$sClassFile)) ;
	}
	
	/**
	 * @return org\jecat\framework\pattern\composite\IContainer
	 */
	public function aspects()
	{
		if( !$this->aAspects )
		{
			$this->aAspects = new Container('org\\jecat\\framework\\lang\\aop\\Aspect') ;
		}
	
		return $this->aAspects ;
	}
	
	public function isValid()
	{
		foreach( $this->aspectIterator() as $aAspect )
		{
			if( !$aAspect->isValid() )
			{
				return false ;
			}
		}
		return true ;
	}
	
	public function serialize()
	{
		$arrData = array(
				'arrAspectClasses' => &$this->arrAspectClasses ,
				'aAspects' => $this->aAspects ,
				'sAspectLibSignture' => &$this->sAspectLibSignture ,
		) ;
		
		return serialize($arrData) ;
	}
	
	public function unserialize($serialized)
	{
		$this->__construct() ;
	
		$arrData = unserialize($serialized) ;
		$this->arrAspectClasses =& $arrData['arrAspectClasses'] ;
		$this->aAspects =& $arrData['aAspects'] ;
		$this->sAspectLibSignture =& $arrData['sAspectLibSignture'] ;
	}
	
	private $arrAspectClasses ;
	
	private $aAspects ;
	
	private $aPointcutIterator ;
	private $aJointPointIterator ;
	
	private $sAspectLibSignture ;
}


