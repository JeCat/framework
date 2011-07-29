<?php
namespace jc\fs\imp ;

use jc\fs\FileSystem;

class LocalFileSystem extends FileSystem
{
	public function __construct($sLocalPath)
	{
		$this->sLocalPath = self::formatPath($sLocalPath) ;
	}
	
	public function iterator($sPath)
	{
		
	}
	
	public function localPath()
	{
		return $this->sLocalPath ;
	}
	
	/////////////////////////////////////////////////////////////////////////
	
	protected function existsOperation(&$sPath)
	{
		return file_exists($this->sLocalPath.$sPath) ;		
	}
	
	protected function isFileOperation(&$sPath)
	{
		return is_file($this->sLocalPath.$sPath) ;		
	}
	
	protected function isFolderOperation(&$sPath)
	{
		return is_dir($this->sLocalPath.$sPath) ;
	}
	
	protected function deleteFileOperation(&$sPath)
	{
		return unlink($this->sLocalPath.$sPath) ;
	}
	
	protected function deleteDirOperation(&$sPath)
	{
		return rmdir($this->sLocalPath.$sPath) ;
	}
	
	protected function createFileObject(&$sPath)
	{
		return LocalFile::createInstance( array($this,$sPath,$this->sLocalPath.$sPath), $this->application() ) ;
	}
	
	protected function createFolderObject(&$sPath)
	{
		return LocalFolder::createInstance( array($this,$sPath,$this->sLocalPath.$sPath), $this->application() ) ;
	}
	
	protected function copyOperation(&$sPath,FileSystem $aToFs,&$sToPath)
	{
		// 本地系统文件之间的操作
		if( $aToFs instanceof self )
		{
			return copy( $this->localPath().$sPath, $aToFs->localPath().$sToPath ) ;
		}
		
		// 不同类型文件系统之间的操作
		else 
		{
			// todo
		}
	}
	
	protected function moveOperation(&$sPath,FileSystem $aToFs,&$sToPath)
	{
		// 本地系统文件之间的操作
		if( $aToFs instanceof self )
		{
			return rename( $this->localPath().$sPath, $aToFs->localPath().$sToPath ) ;
		}
		
		// 不同类型文件系统之间的操作
		else 
		{
			// todo
		}
	}
	
	private $sLocalPath ;
}

?>