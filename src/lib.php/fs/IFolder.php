<?php
namespace jc\fs ;

interface IFolder extends IFSO
{
	/**
	 * @return \IFSO
	 */
	public function find($sPath) ;
	
	public function createFile() ;
	
	public function createFolder() ;
	
	/**
	 * @return \Iterator
	 */
	public function iterator() ;
}

?>