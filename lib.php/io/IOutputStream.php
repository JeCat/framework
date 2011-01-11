<?php
namespace jc\io ;

interface IOutputStream
{
	/**
	 * Enter description here ...
	 * 
	 * @return void
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