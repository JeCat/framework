<?php
namespace jc\setting\imp ;

use jc\setting\IKey;
use jc\setting\Setting;

class FsSetting extends Setting
{
	public function __construct(IFolder $aRootFolder)
	{
		$this->aRootFolder = $aRootFolder ;
	}
	
	/**
	 * @return IKey 
	 */
	public function key($sPath)
	{
		$sPath = self::trimRootSlash($sPath) ;
		
		return;
	}
	
	public function createKey($sPath)
	{
		$sPath = self::trimRootSlash($sPath) ;
		return ;
	}
	
	public function hasKey($sPath)
	{
		$sPath = self::trimRootSlash($sPath) ;
		
		
		if(!$aFolder = $this->aRootFolder->findFolder($sPath))
		{
			return false;
		}
		$aFolder;
		
	}
	
	public function deleteKey($sPath)
	{
		self::trimRootSlash($sPath) ;
	}
	
	/**
	 * @return \Iterator 
	 */
	public function keyIterator($sPath)
	{
		$sPath = self::trimRootSlash($sPath) ;
		
		if(!$aKey = $this->key($sPath))
		{
			return new \EmptyIterator();
		}
		return $aKey->keyIterator();
	}
	
	static public function trimRootSlash(&$sPath)
	{
		if( substr($sPath,0,1)=='/' and strlen($sPath)>0 )
		{
			$sPath = substr($sPath,1) ;
		}
		return $sPath;
	}
	
	/**
	 * @var jc\fs\IFolder
	 */
	private $aRootFolder ;
}

?>