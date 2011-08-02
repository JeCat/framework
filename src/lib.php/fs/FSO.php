<?php
namespace jc\fs ;

use jc\fs\FileSystem;
use jc\lang\Object;

abstract class FSO extends Object implements IFSO
{
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function __construct(FileSystem $aFileSystem, $sInnerPath='')
	{
		$this->aFileSystem = $aFileSystem ;
		$this->sInnerPath = $sInnerPath ;
	}
	
	/**
	 * @return FileSystem
	 */
	public function fileSystem()
	{
		return $this->aFileSystem ;
	}
	
	public function setFileSystem(FileSystem $aFileSystem)
	{
		$this->aFileSystem = $aFileSystem ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	public function path()
	{
		$sFsMountPath = $this->aFileSystem->mountPath() ;
		return $sFsMountPath=='/'? $this->sInnerPath: ($this->aFileSystem->mountPath().$this->sInnerPath) ;
	}

	public function innerPath()
	{
		return $this->sInnerPath ;
	}
	
	public function setInnerPath($sInnerPath)
	{
		$this->sInnerPath = $sInnerPath ;
	}

	public function dirPath()
	{
		return dirname($this->path()) ;
	}
	
	public function name()
	{
		if(!$this->sName)
		{
			$this->sName = basename($this->path()) ;
		}
		return $this->sName ;
	}
	
	public function title()
	{
		if(!$this->sTitle)
		{
			$this->sTitle = self::getTitlename($this->name()) ;
		}
		return $this->sTitle ;
	}
	
	public function extname()
	{
		if(!$this->sExtname)
		{
			$this->sExtname = self::getExtname($this->name()) ;
		}
		return $this->sExtname ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function delete()
	{
		return $this->fileSystem()->delete($this->path()) ;
	}
	
	/**
	 * @return IFolder
	 */
	public function directory()
	{
		$sPath = $this->path() ;
		if($sPath=='/')
		{
			return null ;
		}
		
		return $this->fileSystem()->rootFileSystem()->findFolder(
			dirname($sPath)
		) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	static public function getExtname($sFilename)
	{
		$nDotIdx = strrpos($sFilename,'.') ;
		return ($nDotIdx===false)? '': substr($sFilename,$nDotIdx+1) ;
	}

	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	static public function getTitlename($sFilename)
	{
		$nDotIdx = strrpos($sFilename,'.') ;
		return ($nDotIdx===false)? $sFilename: substr($sFilename,0,$nDotIdx) ;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see jc\fs.IFSO::httpUrl()
	 */
	public function httpUrl()
	{
		if($this->sHttpUrl)
		{
			return $this->sHttpUrl ;
		}
		
		if( !$aDir=$this->directory() )
		{
			return null ;
		}
		
		if( $this->fileSystem()!=$aDir->fileSystem() )
		{
			return null ;
		}
		
		if( !$sDirHttpUrl = $aDir->httpUrl() )
		{
			return null ;
		}
		
		return $sDirHttpUrl.'/'.basename($this->sInnerPath) ;
	}
	
	public function setHttpUrl($sHttpUrl)
	{
		$this->sHttpUrl = $sHttpUrl ;
	}
	
	private $sInnerPath = "" ;
	private $aFileSystem ;
	private $sName = "" ;
	private $sTitle = "" ;
	private $sExtname = "" ;
	
	private $sHttpUrl ;
}
?>