<?php
namespace jc\resrc ;

use jc\fs\IFolder;

interface IResourceManager
{
	public function addFolder(IFolder $aFolder) ;
	
	public function removeFolder(IFolder $aFolder) ;
	
	public function clearFolders() ;
	
	/**
	 * @return IFile
	 */
	public function find($sFilename) ;

	public function addFilenameWrapper($func) ;

	public function removeFilenameWrapper($func) ;
	
	public function clearFilenameWrappers() ;
	
}

?>