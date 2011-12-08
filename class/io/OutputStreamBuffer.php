<?php

namespace org\jecat\framework\io ;

use org\jecat\framework\lang\Type;

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
	
	public function writePrepend($content)
	{
		if( !empty($this->arrBuffer) and is_string($this->arrBuffer[0]) )
		{
			$this->arrBuffer[0] = $content.$this->arrBuffer[0] ;
		}
		else
		{
			array_unshift($this->arrBuffer,$content) ;
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
			$this->clear() ;
		}
		
		return $sBytes ;
	}
	
	public function clear()
	{
		$this->arrBuffer = array() ;
	}
	
	public function flush()
	{		
		$this->clear() ;
	}
	
	public function isEmpty()
	{
		return empty($this->arrBuffer) ;
	}
	
	private $arrBuffer = array() ;
}

?>