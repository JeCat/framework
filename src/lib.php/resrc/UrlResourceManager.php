<?php
namespace jc\resrc ;

use jc\fs\FileSystem;

use jc\fs\Dir;
use jc\lang\Exception;

class UrlResourceManager extends ResourceManager 
{
	public function addFolder($sPath,$sUrlPrefix=null,$sNamespace='*')
	{
		$sPath = FileSystem::formatPath($sPath).'/' ;
		
		if(!$sUrlPrefix)
		{
			$sAppFolder = $this->application()->applicationDir() ;
			if(!$sAppFolder)
			{
				throw new Exception("没有为系统设置应用目录，无法自动定位资源的访问URL") ;
			}
			
			if( strstr($sPath,$sAppFolder)===false )
			{
				throw new Exception(
					"资源目录(%s)不在系统目录内，无法自动定位资源的访问URL，必须为该资源目录显式地指定一个URL访问路径。"
					, $sPath
				) ;
			}
			
			$sAppFolderPathLen = strlen($sAppFolder) ;
			if( $sAppFolderPathLen==strlen($sPath) )
			{
				$sUrlPrefix = '' ;
			}
			else 
			{
				$sUrlPrefix = substr($sPath,$sAppFolderPathLen) ;
			}
		}
		
		parent::addFormatFolder($sPath,$sNamespace) ;
		$this->arrFolderUrlPrefix [$sPath] = $sUrlPrefix ;
	}
	
	public function removeFolder($sPath,$sNamespace='*')
	{
		$sPath = FileSystem::formatPath($sPath).'/' ;
		
		unset($this->arrFolderUrlPrefix [$sPath]) ;
		
		parent::removeFolder($sPath,$sNamespace) ;
	}
	
	public function clearFolders($sNamespace='*')
	{
		parent::clearFolders($sNamespace) ;
		
		$this->arrFolderUrlPrefix = array() ;
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
			return $this->arrFolderUrlPrefix[$sFolderPath] . $sWrapedFilename ;
		}
		else 
		{
			return null ;
		}
	}
	
	public function findByPath()
	{
		
	}
	
	private $arrFolderUrlPrefix = array() ;
}

?>