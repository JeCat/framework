<?php
namespace jc\io ;

use jc\lang\Object;

class OutputStream extends Stream implements IOutputStream
{
	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	public function write($sBytes,$nLen=null)
	{
		return fwrite($this->hHandle,$nLen) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function flush()
	{
		fflush($this->hHandle) ;
	}
	
}
?>