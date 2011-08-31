<?php
namespace jc\lang\aop ;

use jc\lang\Exception;

use jc\lang\oop\Package;

use jc\lang\compile\CompilerFactory;

use jc\system\ClassLoader;
use jc\pattern\composite\Container;
use jc\lang\Object;

class AOP extends Object
{	
	/**
	 * @return jc\pattern\IContainer
	 */
	public function aspects()
	{
		if( !$this->aAspects )
		{
			$this->aAspects = new Container('jc\\aop\\Aspect') ;
		}
		
		return $this->aAspects ;
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
		
		$aAspect = Aspect::createFromToken($aClassToken,$aTokenPool) ;
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