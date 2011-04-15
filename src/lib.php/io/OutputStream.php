<?php
namespace jc\io ;

use jc\lang\Object;

class OutputStream extends Stream implements IOutputStream, ILockable
{
	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	public function write($Contents,$nLen=null)
	{
		return fwrite($this->hHandle,strval($Contents),$nLen) ;
	}

	public function bufferBytes()
	{
		return '' ;
	}
	
	public function clean()
	{ }
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function flush()
	{
		fflush($this->hHandle) ;
	}

	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function lock()
	{
		flock($this->hHandle,LOCK_EX) ;
	}
}
?>