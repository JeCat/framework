<?php

namespace org\jecat\framework\io ;

use org\jecat\framework\lang\Type;

class OutputStreamBuffer extends OutputStream implements IRedirectableStream, IBuffRemovable
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
	
	public function removeBuff($content)
	{
		$pos=array_search($content,$this->arrBuffer,is_object($content)) ;
		if( $pos!==false )
		{
			unset($this->arrBuffer[$pos]) ;
		}
	}
	
	public function & bufferRawDatas()
	{
		return $this->arrBuffer ;
	}
	
	public function redirect(IOutputStream $aOutputStream=null)
	{
		// 从原来的重定向目标设备中解除
		if( $this->aRedirectionDev and $this->aRedirectionDev instanceof IBuffRemovable )
		{
			$this->aRedirectionDev->removeBuff($this) ;
		}
		
		// 重定向到新的目标
		if($aOutputStream)
		{
			$aOutputStream->write($this) ;
		}
		$this->aRedirectionDev = $aOutputStream ;
	}
	
	public function redirectionDev()
	{
		return $this->aRedirectionDev ;
	}
	
	protected $arrBuffer = array() ;
	
	private $aRedirectionDev ;
}

?>