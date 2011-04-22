<?php

namespace jc\ui ;

use jc\pattern\composite\CompositeObject;

abstract class Object extends CompositeObject implements IObject
{
	public function __construct()
	{
		$this->addChildTypes(__CLASS__) ;
	}

	// implement for IObject //////////////////
	/**
	 * @return IObject
	 */
	public function parent()
	{
		return $this->aParent ;
	}
	
	public function setParent(IObject $aParent)
	{
		$this->aParent = $aParent ;
	}
	
	public function depth()
	{
		$aParent = $this->parent() ;
		return $aParent? $aParent->depth()+1: 0 ;
	}
	
	// implement for IContainedable //////////////////
	static public function type()
	{
		return __CLASS__ ;
	}
	
	private $aParent ;
	
}

?>