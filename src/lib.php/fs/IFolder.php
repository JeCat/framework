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
	public function createFile($sPath,$nMode=FileSystem::CREATE_FILE_DEFAULT) ;
	
	/**
	 * @return IFolder
	 */
	public function createFolder($sPath,$nMode=FileSystem::CREATE_FOLDER_DEFAULT) ;
	
	/**
	 * @return \Iterator
	 */
	public function iterator($nFlag=FSIterator::FLAG_DEFAULT) ;
}

?>
