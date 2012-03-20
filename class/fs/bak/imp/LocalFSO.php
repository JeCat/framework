<?php

namespace org\jecat\framework\fs\imp ;

use org\jecat\framework\fs\IFolder;
use org\jecat\framework\fs\IFile;
use org\jecat\framework\lang\Type;
use org\jecat\framework\fs\Folder;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\fs\FSO;

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
		$nOldMark = umask(0) ;
		$bRes = chmod($this->sLocalPath,$nMode) ;
		umask($nOldMark) ;
		
		return $bRes ;
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
				throw new \org\jecat\framework\lang\Exception('复制目标已存在，无法复制');
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
				throw new \org\jecat\framework\lang\Exception('复制目标已存在，无法复制');
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
	
	public function url($bProtocol=true) 
	{
		if($bProtocol)
		{
			return 'file://' . $this->localPath() ;
		}
		else
		{
			return $this->localPath() ;
		}
	}
	
	private $sLocalPath = "" ;
}
?>
