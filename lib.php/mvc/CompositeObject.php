<?php

class CompositeObject extends \jc\Object implements ICompositable
{
	// implement for IContainedable //////////////////
	public function addName($Names)
	{
		$Names = (array) $Names ;
		foreach ($Names as $sName)
		{
			if( !in_array($sName, $this->arrNames) )
			{
				return $this->arrNames[] = (string)$sName ;
			}
		}
	}
	
	public function hasName($sName)
	{
		return in_array($sName, $this->arrNames) ;
	}
	
	public function remove($sName)
	{
		$nIdx = array_search($sName, $this->arrNames) ;
		if($nIdx!==null)
		{
			unset($this->arrNames[$nIdx]) ;
		}
		
		return $nIdx ;
	}
	
	public function clearNames()
	{
		$this->arrNames = array() ;
	}
	
	public function namesIterator()
	{
		return $this->arrNames ;
	}
	
	
	
	// implement for IContainedable //////////////////
	public function setParent(IContainer $aParent)
	{
		$this->aParent = $aParent ;
	}
	
	public function parent()
	{
		return $this->aParent ;
	}
	
	public function setChildTypes(array $arrTypes) {
		// TODO Auto-generated method stub
		
	}
	
	// implement for IContainer //////////////////
	public function addChild(IContainedable $aChild) {
		// TODO Auto-generated method stub
		
	}

	public function removeChild($Child) {
		// TODO Auto-generated method stub
		
	}

	public function clearChildren() {
		// TODO Auto-generated method stub
		
	}

	public function child($sName) {
		// TODO Auto-generated method stub
		
	}

	public function findChildInFamily($sName) {
		// TODO Auto-generated method stub
		
	}


	public function childrenIterator($Types = null) {
		// TODO Auto-generated method stub
	}
	
	private $arrNames = array() ;
	
	private $aParent = null ;

	private $arrChildren = array() ;

}

?>