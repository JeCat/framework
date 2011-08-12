<?php
namespace jc\fs\imp ;

use jc\fs\imp\Local\FileSystem;

class MockLocalFileSystem extends LocalFileSystem
{
	public function __construct($sLocalPath)
	{
		parent::__construct($sLocalPath);
	}
	
	public function iterator($sPath)
	{
		parent::iterator($sPath);
	}
	
	public function localPath()
	{
		return parent::localPath();
	}
	
	/////////////////////////////////////////////////////////////////////////
	
	public function existsOperation(&$sPath)
	{
		return parent::existsOperation($sPath);
	}
	
	public function isFileOperation(&$sPath)
	{
		return parent::isFileOperation($sPath);
	}
	
	public function isFolderOperation(&$sPath)
	{
		return parent::isFolderOperation($sPath);
	}
	
	public function deleteFileOperation(&$sPath)
	{
		return parent::deleteFileOperation($sPath);
	}
	
	public function deleteDirOperation(&$sPath)
	{
		return parent::deleteDirOperation($sPath);
	}
	
	public function createFileObject(&$sPath)
	{
		return parent::createFileObject($sPath);
	}
	
	public function createFolderObject(&$sPath)
	{
		return parent::createFolderObject($sPath);
	}
}

?>
