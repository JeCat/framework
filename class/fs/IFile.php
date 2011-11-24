<?php
namespace org\jecat\framework\fs ;

interface IFile extends IFSO
{
	/**
	 * Enter description here ...
	 * 
	 * @return org\jecat\framework\io\IOutputStream
	 */
	public function openWriter() ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return org\jecat\framework\io\IInputStream
	 */
	public function openReader() ;
	
	public function length() ;
	
	public function hash() ;
	
	public function includeFile($bOnce=false,$bRequire=false) ;
}

?>