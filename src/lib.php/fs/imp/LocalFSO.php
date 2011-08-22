<?php

namespace jc\fs\imp ;

use jc\fs\IFolder;
use jc\fs\IFile;
use jc\lang\Type;
use jc\fs\FileSystem;
use jc\lang\Exception;
use jc\fs\FSO;

abstract class LocalFSO extends FSO
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

	public function copy($to)
	{
		if( $this instanceof IFile ){
			if( is_string($to) ){
				$toFSO = $this->fileSystem()->rootFileSystem()->createFile( $to ,FileSystem::CREATE_ONLY_OBJECT) ;
			}else if($to instanceof FSO){
				$toFSO = $to;
			}else{
				return parent::copy($to);
			}
			if( $toFSO -> exists() ){
				throw new \jc\lang\Exception('复制目标已存在，无法复制');
			}
			if($toFSO instanceof IFile and $toFSO instanceof LocalFSO ){
				// php 原生copy函数：如果目标文件已存在，将会被覆盖。
				// http://http://www.php.net/manual/zh/function.copy.php
				copy($this->localPath(),$toFSO->localPath());
				return $toFSO;
			}else{
				return parent::copy($to);
			}
		}else{
			return parent::copy($to);
		}
	}
	
	public function move($to)
	{
		if( $this instanceof IFile ){
			if( is_string($to) ){
				$toFSO = $this->fileSystem()->rootFileSystem()->createFile( $to ,FileSystem::CREATE_ONLY_OBJECT) ;
			}else if($to instanceof FSO){
				$toFSO = $to;
			}else{
				return parent::copy($to);
			}
			if( $toFSO -> exists() ){
				throw new \jc\lang\Exception('复制目标已存在，无法复制');
			}
			if($toFSO instanceof IFile and $toFSO instanceof LocalFSO ){
				rename($this->localPath(),$toFSO->localPath());
				return $toFSO;
			}else{
				return parent::copy($to);
			}
		}else{
			return parent::copy($to);
		}
	}
	
	public function url() 
	{
		return 'file://' . $this->localPath() ;
	}
	
	private $sLocalPath = "" ;
}
?>
