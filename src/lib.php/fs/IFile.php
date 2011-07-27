<?php
namespace jc\fs ;

interface IFile extends IFSO
{
	/**
	 * Enter description here ...
	 * 
	 * @return io\IOutputStream
	 */
	public function openWriter() ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return io\IInputStream
	 */
	public function openReader() ;
	
	public function length() ;
	
	public function includeFile() ;
}

?>