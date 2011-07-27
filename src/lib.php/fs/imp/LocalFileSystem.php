<?php
namespace jc\fs\imp ;

use jc\fs\FileSystem;

class LocalFileSystem extends FileSystem
{
	public function __construct($sLocalPath)
	{
		$this->sLocalPath = self::formatPath($sLocalPath) ;
	}
	
	public function copy($sFromPath,$sToPath)
	{
		
	}
	
	public function move($sFromPath,$sToPath)
	{
		
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

	protected function createFileOperation(&$sPath,&$nMode)
	{
		$sLocalPath = $this->localPath().$sPath ;
		
		if( !$hHandle=fopen($sLocalPath,'w') )
		{
			return false ;
		}
		
		fclose($hHandle) ;
		
		chmod($sLocalPath, $nMode) ;
		
		return true ;
	}
	
	protected function createFolderOperation(&$sPath,&$nMode,&$bRecursive)
	{
		return mkdir($this->localPath().$sPath,$nMode,$bRecursive) ;
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
	
	
	private $sLocalPath ;
}

?>