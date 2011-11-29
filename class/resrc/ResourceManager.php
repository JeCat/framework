<?php
namespace org\jecat\framework\resrc ;

use org\jecat\framework\fs\FileSystem;

use org\jecat\framework\fs\IFolder;
use org\jecat\framework\lang\Object;

class ResourceManager extends Object implements \Serializable
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
	 * @return org\jecat\framework\fs\IFile
	 */
	public function find($sFilename,$sNamespace='*')
	{
		if( strstr($sFilename,':')!==false )
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

	public function serialize()
	{
		$arrData = array(
			'arrFolders' => array() ,
		) ;
		
		foreach($this->arrFolders as $sNamespace=>$arrFolders)
		{
			foreach($arrFolders as $aFolder)
			{
				$arrData['arrFolders'][$sNamespace][] = $aFolder->path() ;
			}
		}
		
		return serialize($arrData) ;
	}

	public function unserialize($serialized)
	{
		$this->__construct() ;
		
		$arrData = unserialize($serialized) ;
		foreach($arrData['arrFolders'] as $sNamespace=>$arrFolders)
		{
			foreach($arrFolders as $sFolderPath)
			{
				$aFolder = FileSystem::singleton()->findFolder($sFolderPath) ;
				$this->addFolder($aFolder,$sNamespace) ;
			}
		}
	}

	public function folderNamespacesIterator()
	{
		return new \ArrayIterator(array_keys($this->arrFolders)) ;
	}
	public function folderIterator($sNamespace='*')
	{
		return isset($this->arrFolders[$sNamespace])?
				new \ArrayIterator($this->arrFolders[$sNamespace]):
				new \EmptyIterator() ;
	}
	
	protected $arrFolders = array() ;
	
	private $arrFilenameWrappers = array() ;
}

?>