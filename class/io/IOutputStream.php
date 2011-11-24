<?php
namespace jc\io ;

interface IOutputStream 
{
	public function write($sBytes,$nLen=null,$bFlush=false) ;
	
	public function bufferBytes() ;
	
	public function clean() ;
	
	public function flush() ;
	
}
?>