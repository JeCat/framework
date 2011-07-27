<?php

namespace jc\fs\imp ;

use jc\fs\FSO;

class LocalFSO extends FSO
{
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function __construct(LocalFileSystem $aFileSystem,$sPath,$sLocalPath)
	{
		$this->sLocalPath = $sLocalPath ;
		
		parent::__construct($aFileSystem,$sPath) ;
	}
	
	public function localPath()
	{
		return $this->sLocalPath ;
	}
		
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function canRead()
	{
		return is_readable($this->sLocalPath) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function canWrite()
	{
		return is_writeable($this->sLocalPath) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function canExecute()
	{
		return is_executable($this->sLocalPath) ;
	}

	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	public function perms()
	{
		return fileperms($this->sLocalPath) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function setPerms($nMode)
	{
		return chmod($this->sLocalPath,$nMode) ;
	}
	
	public function createTime()
	{
		return filectime($this->sLocalPath) ;
	}
	
	public function modifyTime()
	{
		return filemtime($this->sLocalPath) ;
	}
	
	public function accessTime()
	{
		return fileatime($this->sLocalPath) ;
	}
	
	public function isHidden()
	{
		return false ;
	}
	
	public function exists()
	{
		return file_exists($this->sLocalPath) ;
	}
	
	private $sLocalPath = "" ;
}
?>