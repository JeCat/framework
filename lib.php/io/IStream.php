<?php
namespace jc\io ;

interface IStream
{
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function supportsLock() ;
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function lock() ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function unlock() ;
	
}
?>