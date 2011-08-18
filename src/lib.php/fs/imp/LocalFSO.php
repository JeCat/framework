<?php

namespace jc\fs\imp ;

use jc\fs\IFolder;
use jc\fs\IFile;
use jc\lang\Type;

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
		if ( ( is_string($to) and $this->fileSystem()->rootFileSystem()->exists($to) ) 
				or ( ( $to instanceof IFile or $to instanceof IFolder) and $to->exists() ) ){
			throw new \jc\lang\Exception('复制目标已存在，无法复制');
		}
		if ( is_string($to) ){
			if( $this instanceof IFile ){
				$to = $this->fileSystem()->rootFileSystem()->createFile($to) ;
			}else if( $this instanceof IFolder ){
				$to = $this->fileSystem()->rootFileSystem()->createFolder($to) ;
			}else{
				throw new \jc\lang\Exception('this即不是IFile也不是IFolder');
			}
		}
		if( $this instanceof IFile and $to instanceof IFile and $to instanceof LocalFSO ){
			copy($this->localPath(),$to->localPath());
			return $to;
		}else{
			return parent::copy($to);
		}
	}
	
	public function move($to)
	{
		if ( ( is_string($to) and $this->fileSystem()->rootFileSystem()->exists($to) ) 
				or ( ( $to instanceof IFile or $to instanceof IFolder) and $to->exists() ) ){
			throw new \jc\lang\Exception('复制目标已存在，无法复制');
		}
		if ( is_string($to) ){
			if( $this instanceof IFile ){
				$to = $this->fileSystem()->rootFileSystem()->createFile($to) ;
			}else if( $this instanceof IFolder ){
				$to = $this->fileSystem()->rootFileSystem()->createFolder($to) ;
			}else{
				throw new \jc\lang\Exception('this即不是IFile也不是IFolder');
			}
		}
		if( $this instanceof IFile and $to instanceof IFile and $to instanceof LocalFSO ){
			rename($this->localPath(),$to->localPath());
			return $to;
		}else{
			return parent::move($to);
		}
	}
	
	public function url() 
	{
		return 'file://' . $this->localPath() ;
	}
	
	private $sLocalPath = "" ;
}
?>
