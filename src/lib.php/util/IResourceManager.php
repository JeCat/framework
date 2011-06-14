<?php
namespace jc\util ;

interface IResourceManager
{
	public function addFolder($sPath) ;
	
	public function removeFolder($sPath) ;
	
	public function clearFolders() ;
	
	public function find($sFilename) ;

	public function addFilenameWrapper($func) ;

	public function removeFilenameWrapper($func) ;
	
	public function clearFilenameWrappers() ;
	
}

?>