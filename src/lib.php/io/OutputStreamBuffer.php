<?php

namespace jc\io ;

use jc\lang\Type;

class OutputStreamBuffer extends OutputStream
{
	public function __construct(IOutputStream $aPhysicalStream=null)
	{
		$this->aPhysicalStream = $aPhysicalStream?:$this->application(true)->response()->printer() ;
	}
	
	public function write($Content,$nLen=null,$bFlush=false)
	{
		$nIdx = count($this->arrBuffer)-1 ;
		
		if( $nIdx>0 and is_string($this->arrBuffer[$nIdx]) and !($Content instanceof IOutputStream) )
		{
			$this->arrBuffer[$nIdx]+= strval($Content) ;
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
	
	public function bufferBytes()
	{
		$sBytes = '' ;
		
		foreach ($arrBuffer as $Contents)
		{
			if( is_string($Contents) )
			{
				$sBytes+= $Contents ;
			}
			
			else
			{
				$sBytes+= $Contents->bufferBytes() ;
			}
		}
		
		return $sBytes ;
	}
	
	public function clean()
	{
		$this->arrBuffer = array() ;
	}
	
	public function flush()
	{
		foreach ($arrBuffer as $Contents)
		{
			if( is_string($Contents) )
			{
				$this->aPhysicalStream->write($Contents,null,true) ;
			}
			
			else
			{
				$this->aPhysicalStream->write($Contents->bufferBytes(),null,true) ;
				$Contents->clean() ;
			}
		}
		
		$this->clean() ;
	}
	
	
	private $arrBuffer = array() ;
	
	private $aPhysicalStream ;
}

?>