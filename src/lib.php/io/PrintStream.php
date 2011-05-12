<?php
namespace jc\io ;


class PrintStream extends OutputStream
{
	public function __construct()
	{
		Stream::__construct( fopen('php://stdout','w') ) ;
	}

	public function __destruct()
	{
		ob_flush() ;
	}
	
	public function printvar($Variable)
	{
		$this->write(print_r($Variable,true)) ;
	}
	
	public function println($sBytes)
	{
		$this->write($sBytes."\r\n") ;
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
	
	public function catchAllStdOutput()
	{
		
	}
}
?>