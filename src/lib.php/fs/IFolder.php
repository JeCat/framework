<?php
namespace jc\fs ;

interface IFolder extends IFSO
{
	/**
	 * @return IFile
	 */
	public function findFile($sPath) ;
	
	/**
	 * @return IFolder
	 */
	public function findFolder($sPath) ;
	
	/**
	 * @return IFile
	 */
	public function createFile($sPath) ;
	
	/**
	 * @return IFolder
	 */
	public function createFolder($sPath) ;
	
	/**
	 * @return \Iterator
	 */
	public function iterator() ;
}

?>