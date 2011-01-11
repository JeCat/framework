<?php
namespace jc\io ;

interface IOutputStream extends IStream
{
	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	public function write($sBytes) ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function flush() ;
	
}
?>