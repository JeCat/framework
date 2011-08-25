<?php
namespace jc\lang\aop ;

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
		$this->classLoader()->searchClass($sAspectName) ;
	}
	
	/**
	 * jc\system\ClassLoader
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