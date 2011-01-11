<?php
namespace jc\io ;

interface IInputStream extends IStream
{
	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	function read($nBytes) ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	function readInString($nBytes) ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	function reset() ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	function available() ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	function seek($nPosition) ;	
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function isEnd() ;
	
	
}
?>