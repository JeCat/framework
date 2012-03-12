<?php
namespace org\jecat\framework\lang\aop ;

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
		if( isset($this->arrAspectClasses[$sAspectClass]) )
		{
			return ;
		}
		$this->arrAspectClasses[$sAspectClass] = $sAspectClass ;
		$this->parseAspectClass($sAspectClass) ;
		$this->aPointcutIterator = null ;
		$this->aJointPointIterator = null ;
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
