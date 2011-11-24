<?php
namespace jc\fs ;

interface IFile extends IFSO
{
	/**
	 * Enter description here ...
	 * 
	 * @return jc\io\IOutputStream
	 */
	public function openWriter() ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return jc\io\IInputStream
	 */
	public function openReader() ;
	
	public function length() ;
	
	public function hash() ;
	
	public function includeFile($bOnce=false,$bRequire=false) ;
}

?>