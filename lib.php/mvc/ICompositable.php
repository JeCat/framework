<?php

interface ICompositable
{
	public function addName($Names) ;
	
	public function hasName($sName) ;
	
	public function remove($sName) ;
	
	public function clearNames() ;
	
	public function namesIterator() ;
	
	
	public function setParent(IContainer $aParent) ;
	
	public function parent() ;
	
	
	public function addChild(IContainedable $aChild) ;
	
	public function removeChild($Child) ;
	
	public function clearChildren() ;
	
	public function child($sName) ;
	
	public function findChildInFamily($sName) ;
	
	public function setChildTypes(array $arrTypes) ;
	
	public function childrenIterator($Types=null) ;
}

?>