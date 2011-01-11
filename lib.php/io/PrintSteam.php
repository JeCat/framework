<?php
namespace jc\io ;

use jc\lang\Object;

class PrintSteam extends Object implements IStream, IOutputStream
{
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function printvar($Variable)
	{
		$this->write(print_r($Variable,true)) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function println($sBytes)
	{
		$this->write($sBytes."\r\n") ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function write($sBytes)
	{
		echo $sBytes ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function flush()
	{
		ob_flush() ;
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