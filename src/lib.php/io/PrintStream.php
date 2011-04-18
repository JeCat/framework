<?php
namespace jc\io ;

use jc\lang\Object;

class PrintStream extends Object implements IStream, IOutputStream
{
	public function printvar($Variable)
	{
		$this->write(print_r($Variable,true)) ;
	}
	
	public function println($sBytes)
	{
		$this->write($sBytes."\r\n") ;
	}
	
	public function write($sBytes,$nLen=null,$bFlush=false)
	{
		echo $nLen===null? $sBytes: strlen($sBytes,0,$nLen) ;
		
		if($bFlush)
		{
			$this->flush() ;
		}
	}
	
	public function flush()
	{
		ob_flush() ;
	}
	
	public function bufferBytes()
	{
		return ob_get_contents() ;
	}
	
	public function clean()
	{
		ob_get_clean() ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function supportsLock()
	{
		return false ;
	}
}
?>