<?php

namespace jc\ui ;

use jc\io\IOutputStream;
use jc\pattern\composite\IContainer;
use jc\pattern\composite\CompositeObject;

class Object extends CompositeObject implements IObject
{
	public function __construct()
	{
		$this->addChildTypes('*') ;
	}

	// implement for IObject //////////////////
	/**
	 * @return IObject
	 */
	public function parent()
	{
		return $this->aParent ;
	}
	
	public function setParent(IContainer $aParent)
	{
		$this->aParent = $aParent ;
	}
	
	public function depth()
	{
		$aParent = $this->parent() ;
		return $aParent? $aParent->depth()+1: 0 ;
	}
	
	public function compile(IOutputStream $aDev,ICompiler $aCompiler)
	{
		foreach ($this->childrenIterator() as $aChild)
		{
			$aChild->compile($aDev,$aCompiler) ;
		}
	}
	
	// implement for IContainedable //////////////////
	static public function type()
	{
		return __CLASS__ ;
	}
	
	private $aParent ;
	
}

?>