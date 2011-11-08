<?php
namespace jc\resrc ;

use jc\fs\IFolder;
use jc\lang\Object;

class ResourceManager extends Object
{
	public function addFolder(IFolder $aFolder,$sNamespace='*')
	{
		if(!isset($this->arrFolders[$sNamespace]))
		{
			$this->arrFolders[$sNamespace] = array() ;
		}
		
		if( !in_array($aFolder,$this->arrFolders[$sNamespace],true) )
		{
			array_unshift($this->arrFolders[$sNamespace],$aFolder) ;
		}
	}
	
	public function removeFolder(IFolder $aFolder,$sNamespace='*')
	{
		if(!isset($this->arrFolders[$sNamespace]))
		{
			return ;
		}
		
		$nIdx = array_search($aFolder,$this->arrFolders[$sNamespace],true) ;
		if($nIdx!==false)
		{
			unset($this->arrFolders[$sNamespace][$nIdx]) ;
		}
	}
		
	public function clearFolders($sNamespace='*')
	{
		$this->arrFolders[$sNamespace] = array() ;
	}
	
	/**
	 * @return jc\fs\IFile
	 */
	public function find($sFilename,$sNamespace='*')
	{
		if( $sNamespace=='*' and strstr($sFilename,':')!==false )
		{
			list($sNamespace,$sFilename) = explode(':', $sFilename, 2) ;
		}
		
		if( empty($this->arrFolders[$sNamespace]) )
		{
			return ;
		}
		
		foreach($this->arrFolders[$sNamespace] as $aFolder)
		{
			if( empty($this->arrFilenameWrappers) )
			{
				if( $aFile=$aFolder->findFile($sFilename) )
				{
					return $aFile ;
				}
			}
			else 
			{
				foreach ($this->arrFilenameWrappers as $funcFilenameWrapper)
				{
					$sWrapedFilename = call_user_func_array($funcFilenameWrapper, array($sFilename)) ;
					
					if( $aFile=$aFolder->findFile($sFilename) )
					{
						return $aFile ;
					}
				}
			}
		}
		
		return null ;
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

	public function folderNamespacesIterator()
	{
		return new \jc\pattern\iterate\ArrayIterator(array_keys($this->arrFolders)) ;
	}
	
	public function foldersIterator($sNamespace='*')
	{
		return new \jc\pattern\iterate\ArrayIterator(
			isset($this->arrFolders[$sNamespace])?
				$this->arrFolders[$sNamespace]: array()
		) ;
	}

	public function detectNamespace($sFilename)
	{
		if( ($nPos=strpos($sFilename,':'))!==false )
		{
			return array(substr($sFilename,0,$nPos),substr($sFilename,$nPos+1)) ;
		}
		else 
		{
			return array('*', $sFilename) ;
		}
	}
	
	protected $arrFolders = array() ;
	
	private $arrFilenameWrappers = array() ;
}

?>