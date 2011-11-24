<?php
namespace org\jecat\framework\io ;

interface ILockable
{
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