<?php
namespace jc\util ;

use jc\lang\Object;
use jc\fs\Dir ;
use jc\fs\FSO ;

class ResourceManager extends Object
{
	static public function formatPath($sPath)
	{
		$sNewPath = Dir::formatPath($sPath) ;
		if(!FSO::isFileSystemCaseSensitive())
		{
			$sNewPath = strtolower($sNewPath) ;
		}
		return $sNewPath ;
	} 
	
	public function addFolder($sPath)
	{
		$sNewPath = self::formatPath($sPath) ;
		if( !in_array($sNewPath,$this->arrFolders) )
		{
			array_unshift($this->arrFolders, $sNewPath) ;
		}
	}
	
	public function removeFolder($sPath)
	{
		$sNewPath = self::formatPath($sPath) ;
		$nIdx = array_search($sNewPath, $this->arrFolders) ;
		if($nIdx!==false)
		{
			unset($this->arrFolders[$nIdx]) ;
		}
	}
	
	public function clearFolders()
	{
		$this->arrFolders = array() ;
	}
	
	
	
	private $arrFolders = array() ;
	
}

?>