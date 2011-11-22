<?php
namespace jc\lang\aop ;

use jc\lang\Exception;
use jc\lang\oop\Package;
use jc\lang\compile\CompilerFactory;
use jc\lang\oop\ClassLoader;
use jc\pattern\composite\Container;
use jc\lang\Object;

class AOP extends Object
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
		$this->arrUnparseAspectClasses[] = $sAspectClass ;
	}
	
	/**
	 * @return \Iterator
	 */
	public function aspectIterator() 
	{
		$this->parseRegisteredAspectClass() ;
		
		return $this->aAspects? $this->aspects()->iterator(): new \EmptyIterator() ;
	}
	
	/**
	 * @return \Iterator
	 */
	public function jointPointIterator() 
	{
		$this->parseRegisteredAspectClass() ;
		
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
		$this->parseRegisteredAspectClass() ;
		
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
		$aClassLoader = $this->classLoader() ;
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
	
	public function createClassCompiler()
	{
		$aCompiler = CompilerFactory::createInstance()->create() ;
		$aCompiler->registerGenerator("jc\\lang\\compile\\object\\FunctionDefine","jc\\lang\\aop\\compiler\\FunctionDefineGenerator") ;
		
		return $aCompiler ;
	}
	
	/**
	 * jc\lang\oop\ClassLoader
	 */
	public function classLoader()
	{
		if( !$this->aClassLoader )
		{
			$this->aClassLoader = $this->application()->classLoader() ;
		}
		
		return $this->aClassLoader ;
	}
	
	public function setClassLoader(ClassLoader $aClassLoader)
	{
		$this->aClassLoader = $aClassLoader ;
	}
	
	private function parseRegisteredAspectClass()
	{
		if(empty($this->arrUnparseAspectClasses))
		{
			return ;
		}
		
		foreach($this->arrUnparseAspectClasses as $idx=>&$sAspectClass)
		{
			$this->parseAspectClass($sAspectClass) ;
			unset($this->arrUnparseAspectClasses[$idx]) ;
		}
		
		$this->arrUnparseAspectClasses = null ;
	}
	
	private function parseAspectClass($sAspectClass)
	{
		if( !$aClassFile = $this->classLoader()->searchClass($sAspectClass) )
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
	 * @return jc\pattern\composite\IContainer
	 */
	protected function aspects()
	{
		if( !$this->aAspects )
		{
			$this->aAspects = new Container('jc\\lang\\aop\\Aspect') ;
		}
	
		return $this->aAspects ;
	}
	
	
	private $arrAspectClasses ;
	private $arrUnparseAspectClasses ;
	
	private $aAspects ;
	
	private $aClassLoader ;
}
