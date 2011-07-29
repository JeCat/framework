<?php
namespace jc\fs ;

interface IFSO
{

	public function path() ;
	
	public function dirPath() ;

	public function name() ;

	public function title() ;
	
	public function extname() ;
	
	/**
	 * @return FileSystem
	 */
	public function fileSystem() ;
	
	public function setFileSystem(FileSystem $aFileSystem) ;

	public function canRead() ;
	
	public function canWrite() ;
	
	public function canExecute() ;

	function perms() ;
	
	public function setPerms($nMode) ; 
	
	public function delete() ;
	
	public function copy($to) ;
	
	public function move($to) ;
	
	public function createTime() ;
	
	public function modifyTime() ;
	
	public function accessTime() ;
	
	public function isHidden() ;
	
	public function exists() ;
	
	public function create() ;
	
	public function setInnerPath($sPath) ;
}

?>