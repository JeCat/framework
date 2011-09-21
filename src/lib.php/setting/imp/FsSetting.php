<?php
namespace jc\setting\imp ;


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
		self::trimRootSlash($sPath) ;
		
		
	}
	
	public function createKey($sPath)
	{
		self::trimRootSlash($sPath) ;
	}
	
	public function hasKey($sPath)
	{
		self::trimRootSlash($sPath) ;
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
		
	}
	
	
	
	
	static public function trimRootSlash(&$sPath)
	{
		if( substr($sPath,0,1)=='/' and strlen($sPath)>0 )
		{
			$sPath = substr($sPath,1) ;
		}
	}
	
	
	private $aRootFolder ;
}

?>