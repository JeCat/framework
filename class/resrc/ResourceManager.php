<?php
namespace org\jecat\framework\resrc ;

use org\jecat\framework\fs\File;

use org\jecat\framework\fs\Folder;
use org\jecat\framework\lang\Object;

class ResourceManager extends Object implements \Serializable
{
	public function addFolder(Folder $aFolder,$sNamespace='*')
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
	
	public function removeFolder(Folder $aFolder,$sNamespace='*')
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
	 * @return org\jecat\framework\fs\File
	 */
	public function find($sFilename,$sNamespace='*')
	{
		list($aFolder,$sFilename)=$this->findEx($sFilename,$sNamespace) ;
		if( !$sFilename )
		{
			return null ;
		}
		
		return new File($aFolder->path().'/'.$sFilename) ;
	}
	
	/**
	 * @return array(org\jecat\framework\fs\Folder,string)
	 */
	public function findEx($sFilename,$sNamespace='*')
	{
		if( strstr($sFilename,':')!==false )
		{
			list($sNamespace,$sFilename) = explode(':', $sFilename, 2) ;
		}
		
		if( empty($this->arrFolders[$sNamespace]) )
		{
			return array(null,null) ;
		}
		
		foreach($this->arrFolders[$sNamespace] as $aFolder)
		{
			if( empty($this->arrFilenameWrappers) )
			{
				if( is_file($aFolder->path().'/'.$sFilename) )
				{
					return array($aFolder,$sFilename) ;
				}
			}
			else
			{
				foreach ($this->arrFilenameWrappers as $funcFilenameWrapper)
				{
					$sWrapedFilename = call_user_func_array($funcFilenameWrapper, array($sFilename)) ;
					
					if( is_file($aFolder->path().'/'.$sWrapedFilename) )
					{
						return array($aFolder,$sWrapedFilename) ;
					}
				}
			}
		}
		
		return array(null,null) ;
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
		return serialize($this->arrFolders) ;
	}
	
	public function unserialize($serialized)
	{
		$this->__construct() ;
		$this->arrFolders = unserialize($serialized) ;
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