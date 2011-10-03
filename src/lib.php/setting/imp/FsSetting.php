<?php
namespace jc\setting\imp;

use jc\fs\IFolder;
use jc\setting\IKey;
use jc\setting\imp\FsKey;
use jc\setting\Setting;

class FsSetting extends Setting
{
	public function __construct(IFolder $aRootFolder)
	{
		$this->aRootFolder = $aRootFolder;
	}
	
	/**
	 * @return IKey 
	 */
	public function key($sPath)
	{
		$sPath = self::trimRootSlash ( $sPath );
		
		if($aFolder = $this->aRootFolder->findFolder ( $sPath ))
		{
			return new FsKey($aFolder);
		}
		return null;
	}
	
	public function createKey($sPath)
	{
		$sPath = self::trimRootSlash ( $sPath );
		if ($this->hasKey ( $sPath ))
		{
			return $this->key ( $sPath );
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
			return $aFolderToDel->delete ();
		}
		return false;
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
		if (substr ( $sPath, 0, 1 ) == '/' and strlen ( $sPath ) > 0)
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