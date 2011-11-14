<?php
namespace jc\setting\imp;

use jc\pattern\iterate\ReverseIterator;
use jc\fs\FSIterator;
use jc\fs\IFolder;
use jc\setting\IKey;
use jc\setting\imp\FsKey;
use jc\setting\Setting;

class FsSetting extends Setting
{
	/**
	 * 
	 * @param IFolder $aRootFolder
	 */
	public function __construct(IFolder $aRootFolder)
	{
		$this->aRootFolder = $aRootFolder;
	}
	
	public function createKey($sPath)
	{
		$sPath = self::trimRootSlash ( $sPath );
		
		if ($aFolder = $this->aRootFolder->findFolder ( $sPath ))
		{
			return new FsKey ( $aFolder );
		}
		else
		{
			$aNewFolder = $this->aRootFolder->createFolder ( $sPath );
			return new FsKey ( $aNewFolder );
		}
	}
	
	public function hasKey($sPath)
	{
		$sPath = self::trimRootSlash ( $sPath );
		
		if (! $this->aRootFolder->findFolder ( $sPath ))
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	public function deleteKey($sPath)
	{
		$sPath = self::trimRootSlash ( $sPath );
		
		if ($aFolderToDel = $this->aRootFolder->findFolder ( $sPath ))
		{
//			$bDelWell = 1;
			$aFileIter = $aFolderToDel->iterator ( FSIterator::RECURSIVE_SEARCH | FSIterator::RETURN_FSO | FSIterator::FILE);
			foreach($aFileIter as $aFile)
			{
				$aFile->delete();
//				$bDelWell *= (int)$aFile->delete();
			}
			
			//将文件迭代器反向遍历，前提是迭代器内部机制是浅层文件夹在前，深层文件夹在后
			$aFolderIter = $aFolderToDel->iterator (  FSIterator::RECURSIVE_SEARCH | FSIterator::RETURN_FSO | FSIterator::FOLDER); //
			foreach($aFolderIter as $aFolder)
			{
				$aFolder->delete();
//				$bDelWell *= (int)$aFolder->delete();
			}
			
//			$bDelWell *= (int)$aFolderToDel->delete();
			return true;
		}
		return (bool)$bDelWell;
	}
	
	
	/**
	 * @return \Iterator 
	 */
	public function keyIterator($sPath)
	{
		$sPath = self::trimRootSlash ( $sPath );
		
		if (! $aKey = $this->key ( $sPath ))
		{
			return new \EmptyIterator ();
		}
		return $aKey->keyIterator ();
	}
	
	static public function trimRootSlash(&$sPath)
	{
		if (substr ( $sPath, 0, 1 ) == '/' and strlen ( $sPath ) > 1)
		{
			$sPath = substr ( $sPath, 1 );
		}
		return $sPath;
	}
	
	/**
	 * @var jc\fs\IFolder
	 */
	private $aRootFolder;
}

?>