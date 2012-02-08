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
	
	public function deleteChild($sPath,$bRecurse=false,$bIgnoreError=false) ;
	
	public function delete($bRecurse=false,$bIgnoreError=false) ;
	
	/**
	 * @return \Iterator
	 */
	public function iterator($nFlag=FSIterator::FLAG_DEFAULT) ;
}

?>
