<?php

namespace jc\fs ;

use jc\lang\Object;

class FSO extends Object
{
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function __construct(FileSystem $aFileSystem, $sPath='')
	{
		$this->aFileSystem = $aFileSystem ;
		$this->sPath = $sPath ;
	}
	
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
		return $this->aFileSystem->mountPath() . $this->sPath ;
	}
	
	public function setInnerPath($sPath)
	{
		$this->sPath = $sPath ;
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
	
	public function copy($to)
	{
		return $this->fileSystem()->rootFileSystem()->copy($this,$to) ;
	}
	
	public function move($to)
	{
		return $this->fileSystem()->rootFileSystem()->move($this,$to) ;
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
	
	private $sPath = "" ;
	private $aFileSystem ;
	private $sName = "" ;
	private $sTitle = "" ;
	private $sExtname = "" ;
}
?>