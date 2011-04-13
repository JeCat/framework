<?php

namespace jc\mvc ;


use jc\pattern\composite\CompositeObject;

class NamableObject extends CompositeObject implements INamable
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
	
	
	
	private $arrNames = array() ;
	
}

?>