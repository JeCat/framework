<?php
namespace jc\setting\imp;

use jc\lang\Exception;
use jc\fs\FileSystem;
use jc\pattern\iterate\ReverseIterator;
use jc\fs\FSIterator;
use jc\fs\IFolder;
use jc\setting\IKey;
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

	/**
	 * @return IKey 
	 */
	public function key($sPath,$bCreate=false)
	{
		if( !isset($this->arrKeys[$sPath]) )
		{
			$sItemsPath = self::transPath($sPath) ;
			if( !$aFile=$this->aRootFolder->findFile($sItemsPath,$bCreate?FileSystem::FIND_AUTO_CREATE:0) )
			{
				return null ;
			}
			
			$this->arrKeys[$sPath] = new FsKey($aFile) ;
		}
		
		return $this->arrKeys[$sPath] ;
	}
	
	public function createKey($sPath)
	{
		return $this->key($sPath,true) ;
	}
	
	public function hasKey($sPath)
	{
		return $this->aRootFolder->findFile(self::transPath($sPath))? true: false ;
	}
	
	public function deleteKey($sPath)
	{
		$sFolderPath = self::transPath($sPath,false) ;
		
		if ($aFolderToDel=$this->aRootFolder->findFolder($sFolderPath)) 
		{
			
		}
	}
	
	
	/**
	 * @return \Iterator 
	 */
	public function keyIterator($sPath)
	{
		if ( !$aKey=$this->key($sPath) )
		{
			return new \EmptyIterator ();
		}
		return $aKey->keyIterator ();
	}
	
	static public function transPath($sPath,$bItemsPath=true)
	{
		// 去掉开头的 '/'
		if ( substr($sPath,0,1)=='/' )
		{
			$sPath = strlen($sPath)>1? substr($sPath,1): '' ;
		}
		
		// items.php
		if($bItemsPath)
		{
			if($sPath)
			{
				$sPath.= '/' ;
			}
			
			$sPath.= 'items.php' ;
		}
		
		return $sPath ;
	}
	
	/**
	 * @var jc\fs\IFolder
	 */
	private $aRootFolder;
	
	private $arrKeys = array() ;
}

?>