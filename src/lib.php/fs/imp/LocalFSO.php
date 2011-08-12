<?php

namespace jc\fs\imp ;

use jc\fs\IFolder;

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
		if( $to instanceof IFolder )
		{
			$aToFSO = $to->findFolder( $this->name() ) ;
		}
		else if( is_string($to) )
		{
			$aToFSO = $this->fileSystem()->rootFileSystem()->find($to) ;
		}
		else 
		{
			throw new Exception('参数$from必须为 jc\\fs\\IFSO 或 表示路径的字符串格式，传入的参数格式为 %s',Type::detectType($to)) ;
		}
		
		// 同为 LocalFileSystem ，可直接 copy
		if( $aToFSO instanceof LocalFSO )
		{
			if( copy($this->localPath(),$aToFSO->localPath()) )
			{
				return $aToFSO ;
			}
			else 
			{
				return null ;
			}
		}
	
		// 不同类型文件系统之间的操作
		else 
		{
			// todo
		}
	}
	
	public function move($to)
	{
		$sLocalFile = $this->localPath() ;
		
		if( !file_exists($sLocalFile) )
		{
			return false ;
		}
		
		if( $to instanceof IFolder )
		{
			$aToFSO = $to->findFolder( $this->name() ) ;
		}
		else if( is_string($to) )
		{
			$aToFSO = $this->fileSystem()->rootFileSystem()->find($to) ;
		}
		else 
		{
			throw new Exception('参数$from必须为 jc\\fs\\IFSO 或 表示路径的字符串格式，传入的参数格式为 %s',Type::reflectType($to)) ;
		}
		
		// 同为 LocalFileSystem ，可直接 copy
		if( $aToFSO instanceof LocalFSO )
		{
			
			if( is_uploaded_file($sLocalFile)?	// 如果正在移动的文件是一个来自用户上传的文件，则使用 move_uploaded_file() 移动此文件
					move_uploaded_file($sLocalFile,$aToFSO->localPath()):
					rename($sLocalFile,$aToFSO->localPath())
			)
			{
				// 从原来的文件系统中移除
				$this->fileSystem()->setFSOFlyweight($this->innerPath(),null) ;
				
				return $aToFSO ;
			}
			else 
			{
				return null ;
			}
		}
	
		// 不同类型文件系统之间的操作
		else 
		{
			// todo
		}
	}
	
	public function url() 
	{
		return 'file://' . $this->localPath() ;
	}
	
	private $sLocalPath = "" ;
}
?>
