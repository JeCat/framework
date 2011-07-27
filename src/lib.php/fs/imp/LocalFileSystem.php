<?php
namespace jc\fs\imp ;

use jc\fs\FileSystem;

class LocalFileSystem extends FileSystem
{
	public function __construct($sLocalPath)
	{
		$this->sLocalPath = self::formatPath($sLocalPath) ;
	}
	
	public function exists($sPath)
	{
		file_exists($this->sLocalPath.$sPath) ;		
	}
	
	public function isFile($sPath)
	{
		is_file($this->sLocalPath.$sPath) ;		
	}
	
	public function isFolder($sPath)
	{
		is_dir($this->sLocalPath.$sPath) ;
	}
	
	public function copy($sFromPath,$sToPath)
	{
		
	}
	
	public function move($sFromPath,$sToPath)
	{
		
	}

	public function createFile($sPath,$nMode=0755)
	{
		
	}
	
	public function createFolder($sPath,$nMode=0755)
	{
		return mkdir($sPath) ;
	}
	
	protected function deleteFile($sPath)
	{
		unlink($this->sLocalPath.'/'.$sPath) ;
	}
	
	protected function deleteDir($sPath)
	{
		rmdir($this->sLocalPath.'/'.$sPath) ;
	}
	
	public function iterator($sPath)
	{
		
	}
	
	protected function createFileObject($sPath)
	{
		return LocalFile::createInstance( array($this,$sPath,$this->sLocalPath.$sPath), $this->application() ) ;
	}
	
	protected function createFolderObject($sPath)
	{
		return LocalFolder::createInstance( array($this,$sPath,$this->sLocalPath.$sPath), $this->application() ) ;
	}
	
	public function localPath()
	{
		return $this->sLocalPath ;
	}
	
	private $sLocalPath ;
}

?>