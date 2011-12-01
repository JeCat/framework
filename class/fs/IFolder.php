<?php
namespace org\jecat\framework\fs ;

interface IFolder extends IFSO
{
	/**
	 * @return IFile
	 */
	public function findFile($sPath,$nFlag=0) ;
	
	/**
	 * @return IFolder
	 */
	public function findFolder($sPath,$nFlag=0) ;
	
	/**
	 * @return IFile
	 */
	public function createFile($sPath,$nMode=FileSystem::CREATE_FILE_DEFAULT) ;
	
	/**
	 * @return IFolder
	 */
	public function createFolder($sPath,$nMode=FileSystem::CREATE_FOLDER_DEFAULT) ;
	
	public function deleteChild($sPath) ;
	
	/**
	 * @return \Iterator
	 */
	public function iterator($nFlag=FSIterator::FLAG_DEFAULT) ;
}

?>
