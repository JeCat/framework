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
		$sFsMountedPath = $this->aFileSystem->mountedPath() ;
		return $sFsMountedPath=='/'? $this->sInnerPath: ($sFsMountedPath.$this->sInnerPath) ;
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
		return $this->fileSystem()->directory($this) ;
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
	
	public function copy($to)
	{
		if ( ( is_string($to) and $this->fileSystem()->rootFileSystem()->exists($to) ) 
				or ( ( $to instanceof IFile or $to instanceof IFolder) and $to->exists() ) ){
			throw new \jc\lang\Exception('复制目标已存在，无法复制');
		}
		if ( is_string($to) ){
			if( $this instanceof IFile ){
				$to = $this->fileSystem()->rootFileSystem()->createFile($to) ;
			}else if( $this instanceof IFolder ){
				$to = $this->fileSystem()->rootFileSystem()->createFolder($to) ;
			}else{
				throw new \jc\lang\Exception('this即不是IFile也不是IFolder');
			}
		}
		if( $this instanceof IFile ){
			if ( $to instanceof IFile ){
				$aSrcReader = $this->openReader();
				$aToWriter = $to->openWriter();
				$iBlockSize = 8*1024;
				while( !$aSrcReader -> isEnd() ){
					$str = $aSrcReader->read($iBlockSize);
					$aToWriter -> write( $str );
				}
			}else{
				throw new \jc\lang\Exception('this是IFile而to不是');
			}
		}else if ( $this instanceof IFolder ){
			if( $to instanceof IFolder ){
				// todo
			}else{
				throw new \jc\lang\Exception('this是IFolder而to不是');
			}
		}else{
				throw new \jc\lang\Exception('this即不是IFile也不是IFolder');
		}
	}
	public function move($to){
		copy($to);
		$this->delete();
	}
	
	private $sInnerPath = "" ;
	private $aFileSystem ;
	private $sName = "" ;
	private $sTitle = "" ;
	private $sExtname = "" ;
	
	private $sHttpUrl ;
}
?>
