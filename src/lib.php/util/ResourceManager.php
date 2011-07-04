<?php
namespace jc\util ;

use jc\lang\Object;
use jc\fs\Dir ;
use jc\fs\FSO ;

class ResourceManager extends Object implements IResourceManager
{
	public function addFolder($sPath,$sNamespace='*')
	{
		$sPath = Dir::formatPath($sPath) ;
		$this->addFormatFolder($sPath,$sNamespace) ;
	}
	
	public function removeFolder($sPath,$sNamespace='*')
	{
		$sPath = Dir::formatPath($sPath) ;
		$this->removeFormatFolder($sPath,$sNamespace) ;
	}

	protected function addFormatFolder($sPath,$sNamespace)
	{
		if(!isset($this->arrFolders[$sNamespace]))
		{
			$this->arrFolders[$sNamespace] = array() ;
		}
		
		if( !in_array($sPath,$this->arrFolders[$sNamespace]) )
		{
			array_unshift($this->arrFolders[$sNamespace],$sPath) ;
		}
	}
	
	protected function removeFormatFolder($sPath,$sNamespace)
	{
		if(!isset($this->arrFolders[$sNamespace]))
		{
			return ;
		}
		
		$nIdx = array_search($sPath, $this->arrFolders[$sNamespace]) ;
		if($nIdx!==false)
		{
			unset($this->arrFolders[$sNamespace][$nIdx]) ;
		}
	}
	
	public function clearFolders($sNamespace='*')
	{
		$this->arrFolders[$sNamespace] = array() ;
	}
	
	public function findFolderAndFile($sFilename,$sNamespace='*')
	{
		if( empty($this->arrFolders[$sNamespace]) )
		{
			return ;
		}
		
		foreach($this->arrFolders[$sNamespace] as $sFolderPath)
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
	
	public function find($sFilename,$sNamespace='*')
	{
		if( $sNamespace=='*' and ($nPos=strpos($sFilename,':'))!==false )
		{
			$sNamespace = substr($sFilename,0,$nPos) ;
			$sFilename = substr($sFilename,$nPos+1) ;
		}
		
		list($sFolderPath,$sWrapedFilename) = $this->findFolderAndFile($sFilename,$sNamespace) ;
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
	
	protected $arrFolders = array() ;
	
	private $arrFilenameWrappers = array() ;
}

?>