<?php

namespace jc\pattern\composite ;


class NamableComposite extends Composite implements INamable
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
	
	public function removeName($sName)
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