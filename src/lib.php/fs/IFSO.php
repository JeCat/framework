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
	
	/**
	 * @return IFolder
	 */
	public function directory() ;
	
	/**
	 * 返回 file:///file/path 这样的url
	 */
	public function url() ;
	
	/**
	 * 返回能够通过http访问该文件对象的url——如果该文将对象可以在http上被访问的话
	 * 可以通过 setHttpUrl() http url 设置，如果该文件没有设置 http url，则根据所属目录的 http url 返回自己的 http url。
	 * 只有在所属的目录和该文件属于相同的虚拟文件系统时，才会根据所属目录的http url，返回该文件的http url
	 */
	public function httpUrl() ;
	
	public function setHttpUrl() ;
}

?>