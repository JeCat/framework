<?php
namespace org\jecat\framework\lang\aop ;

use org\jecat\framework\lang\compile\IStrategySummary;
use org\jecat\framework\fs\IFSO;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\oop\Package;
use org\jecat\framework\lang\compile\CompilerFactory;
use org\jecat\framework\lang\oop\ClassLoader;
use org\jecat\framework\pattern\composite\Container;
use org\jecat\framework\lang\Object;

class AOP extends Object implements IStrategySummary
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
		if( !$this->aAspects )
		{
			return new \EmptyIterator() ;
		}
		
		$aIterator = new \AppendIterator() ;
		foreach($this->aspects()->iterator() as $aAspects)
		{
			foreach ($aAspects->pointcuts()->iterator() as $aPointcut)
			{
				$aIterator->append($aPointcut->jointPoints()->iterator()) ;
			}
		}
		
		return $aIterator ;
	}
	
	/**
	 * @return \Iterator
	 */
	public function pointcutIterator() 
	{
		if( !$this->aAspects )
		{
			return new \EmptyIterator() ;
		}
		
		$aIterator = new \AppendIterator() ;
		foreach($this->aspects()->iterator() as $aAspects)
		{
			$aIterator->append($aAspects->pointcuts()->iterator()) ;
		}
		return $aIterator;
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
				$aSrcClassFile = $aClassLoader->searchClass($sBeWeavedClass,ClassLoader::SEARCH_COMPILED) ;
				$aCmpdClassFile = $aClassLoader->searchClass($sBeWeavedClass,ClassLoader::SEARCH_COMPILED) ;
				
				if( !$aSrcClassFile )
				{
					throw new Exception(
						"AOP 无法将目标代码织入到 JointPoint %s 中：没有找到类 %s 的源文件。"
						, array($aJointPoint->$sBeWeavedClass,$aJointPoint->name())
					) ;
				}
				
				if( !$aCmpdClassFile )
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
				
				$aCompiler->compile( $aSrcClassFile->openReader(), $aCmpdClassFile->openWriter() ) ;
				
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
		
		// 根据所有 apect 类的最后修改时间生成签名
		$arrBox = null ;
		$aClassLoader = ClassLoader::singleton() ;
		foreach($this->arrAspectClasses as $sAspectClass)
		{
			if( $aClassFile = $aClassLoader->searchClass($sAspectClass,ClassLoader::SEARCH_SOURCE) )
			{
				$arrBox[$sAspectClass] = $aClassFile->modifyTime() ;
			}
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
		if( !$aClassFile = ClassLoader::singleton()->searchClass($sAspectClass,ClassLoader::SEARCH_SOURCE) )
		{
			throw new Exception("注册到AOP中的Aspace(%s)不存在; Aspace必须是一个有效的类",$sAspectClass) ;
		}
	
		$aClassCompiler = CompilerFactory::singleton()->create() ;
		$aTokenPool = $aClassCompiler->scan($aClassFile->openReader()) ;
		$aClassCompiler->interpret($aTokenPool) ;
	
		if( !$aClassToken=$aTokenPool->findClass($sAspectClass) )
		{
			throw new Exception("根据 class path 搜索到class的定义文件：%s，但是该文件中没有定义：%s",
					array($sAspectClass,$aClassFile->path(),$sAspectClass)
			) ;
		}
	
		$this->aspects()->add(Aspect::createFromToken($aClassToken,$aTokenPool)) ;
	}
	
	/**
	 * @return org\jecat\framework\pattern\composite\IContainer
	 */
	protected function aspects()
	{
		if( !$this->aAspects )
		{
			$this->aAspects = new Container('org\\jecat\\framework\\lang\\aop\\Aspect') ;
		}
	
		return $this->aAspects ;
	}
	
	private $arrAspectClasses ;
	
	private $aAspects ;
	
	private $sAspectLibSignture ;
}
