<?php
namespace jc\io ;

use jc\util\String;

interface IInputStream
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
	function readInString(String $aString,$nBytes) ;
	
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