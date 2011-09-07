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
	 * @return jc\pattern\composite\IContainer
	 */
	public function aspects()
	{
		if( !$this->aAspects )
		{
			$this->aAspects = new Container('jc\\lang\\aop\\Aspect') ;
		}
		
		return $this->aAspects ;
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
		
		$arrJointPointIters = array() ;
		foreach($this->aspects() as $aAspects)
		{
			foreach ($aAspects->pointcuts()->iterator() as $aPointcut)
			{
				$arrJointPointIters[] = $aPointcut->jointPoints()->iterator() ;
			}
		}
		
		return new \RecursiveIteratorIterator(
			new \RecursiveArrayIterator(
				$arrJointPointIters
			)
		) ;
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
		
		$arrJointPointIters = array() ;
		foreach($this->aspects() as $aAspects)
		{
			$arrJointPointIters[] = $aAspects->pointcuts()->iterator() ;
		}
		
		return new \RecursiveIteratorIterator(
			new \RecursiveArrayIterator(
				$arrJointPointIters
			)
		) ;
	}
	
	public function register($sAspectName)
	{
		if( !$aClassFile = $this->classLoader()->searchClass($sAspectName) )
		{
			throw new Exception("指定的class(%s)不存在",$sAspectName) ;
		}
		
		$aClassCompiler = CompilerFactory::singleton()->create() ;
		$aTokenPool = $aClassCompiler->interpret($aClassFile->openReader()) ;

		if( !$aClassToken=$aTokenPool->findClass($sAspectName) )
		{
			throw new Exception("根据 class path 搜索到class的定义文件：%s，但是该文件中没有定义：%s",
				array($sAspectName,$aClassFile->path(),$sAspectName)
			) ;
		}
		
		$this->aspects()->add(Aspect::createFromToken($aClassToken,$aTokenPool)) ;
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
	
	private $aAspects ;
	
	private $aClassLoader ;
}

?>