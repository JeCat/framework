<?php

namespace jc\io ;

use jc\lang\Type;

class OutputStreamBuffer extends OutputStream
{	
	public function write($Content,$nLen=null,$bFlush=false)
	{
		$nIdx = count($this->arrBuffer)-1 ;
		
		if( $nIdx>0 and !is_object($Content) and is_string($this->arrBuffer[$nIdx]) )
		{
			$this->arrBuffer[$nIdx].= strval($Content) ;
		}
		
		else 
		{
			$this->arrBuffer[] = $Content ;
		}
		
		if($bFlush)
		{
			$this->flush() ;
		}
	}
	
	public function __toString()
	{
		return $this->bufferBytes() ;
	}
	
	public function bufferBytes($bClear=true)
	{
		$sBytes = '' ;
		
		foreach ($this->arrBuffer as $Contents)
		{
			$sBytes.= strval($Contents) ;
		}
		
		if($bClear)
		{
			$this->arrBuffer = array() ;
		}
		
		return $sBytes ;
	}
	
	public function clean()
	{
		$this->arrBuffer = array() ;
	}
	
	public function flush()
	{		
		$this->clean() ;
	}
	
	public function isEmpty()
	{
		return empty($this->arrBuffer) ;
	}
	
	private $arrBuffer = array() ;
}

?>