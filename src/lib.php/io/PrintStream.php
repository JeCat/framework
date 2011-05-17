<?php
namespace jc\io ;


class PrintStream extends OutputStream
{
	public function __construct()
	{}

	public function __destruct()
	{}
	
	public function printvar($Variable)
	{
		$this->write(print_r($Variable,true)) ;
	}
	
	public function println($sBytes)
	{
		$this->write($sBytes."\r\n") ;
	}

	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	public function write($Contents,$nLen=null,$bFlush=false)
	{
		echo $nLen===null? strval($Contents): substr(strval($Contents),0,$nLen) ;
		
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
	
	public function supportsLock()
	{
		return false ;
	}
	
}
?>