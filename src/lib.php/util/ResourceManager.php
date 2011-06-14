<?php
namespace jc\util ;

use jc\lang\Object;
use jc\fs\Dir ;
use jc\fs\FSO ;

class ResourceManager extends Object implements IResourceManager
{
	public function addFolder($sPath)
	{
		$sPath = Dir::formatPath($sPath) ;
		$this->addFormatFolder($sPath) ;
	}
	
	public function removeFolder($sPath)
	{
		$sPath = Dir::formatPath($sPath) ;
		$this->removeFormatFolder($sPath) ;
	}

	protected function addFormatFolder($sPath)
	{
		if( !in_array($sPath,$this->arrFolders) )
		{
			array_unshift($this->arrFolders,$sPath) ;
		}
	}
	
	protected function removeFormatFolder($sPath)
	{
		$nIdx = array_search($sPath, $this->arrFolders) ;
		if($nIdx!==false)
		{
			unset($this->arrFolders[$nIdx]) ;
		}
	}
	
	public function clearFolders()
	{
		$this->arrFolders = array() ;
	}
	
	public function findFolderAndFile($sFilename)
	{
		foreach($this->arrFolders as $sFolderPath)
		{
			if( empty($this->arrFilenameWrappers) )
			{
				if( is_file($sFolderPath.$sFilename) )
				{
					return array($sFolderPath,$sFilename) ;
				}
			}
			else 
			{
				foreach ($this->arrFilenameWrappers as $funcFilenameWrapper)
				{
					$sWrapedFilename = call_user_func_array($funcFilenameWrapper, array($sFilename)) ;
				
					if( is_file($sFolderPath.$sWrapedFilename) )
					{
						return array($sFolderPath,$sWrapedFilename) ;
					}
				}
			}
		}
		
		return array(null,null) ;
	}
	
	public function find($sFilename)
	{
		list($sFolderPath,$sWrapedFilename) = $this->findFolderAndFile($sFilename) ;
		if( $sFolderPath and $sWrapedFilename )
		{
			return $sFolderPath.$sWrapedFilename ;
		}
		else 
		{
			return null ;
		}
	}

	public function addFilenameWrapper($func)
	{
		if( !in_array($func, $this->arrFilenameWrappers) )
		{
			$this->arrFilenameWrappers[] = $func ;
		}
	}
	public function removeFilenameWrapper($func)
	{
		$nIdx = array_search($func, $this->arrFilenameWrappers) ;
		if($nIdx!==false)
		{
			unset($this->arrFilenameWrappers[$nIdx]) ;
		}
	}
	public function clearFilenameWrappers()
	{
		$this->arrFilenameWrappers = array() ;
	}
	
	private $arrFolders = array() ;
	
	private $arrFilenameWrappers = array() ;
}

?>