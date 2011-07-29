<?php
namespace jc\fs ;

interface IFSO
{

	public function path() ;
	
	public function dirPath() ;

	public function name() ;

	public function title() ;
	
	public function extname() ;
	
	public function innerPath() ;
	
	public function setInnerPath($sInnerPath) ;
	
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
	
	/**
	 * 复制这个文件对象
	 * @param string,IFolder 		$to	复制目标路径 或 目标目录
	 */
	public function copy($to) ;
	
	/**
	 * 移动这个文件对象
	 * @param string,IFolder 		$to	移动目标路径 或 目标目录
	 */
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