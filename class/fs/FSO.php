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
		
		if( !$sName = $this->name() )
		{
			return $sDirHttpUrl ;
		}
		else 
		{
			if(substr($sDirHttpUrl,strlen($sDirHttpUrl)-1,1)!='/')
			{
				$sDirHttpUrl.= '/' ;
			}
			return $sDirHttpUrl. $sName ;
		}
	}
	
	public function setHttpUrl($sHttpUrl)
	{
		$this->sHttpUrl = $sHttpUrl ;
	}
	
	/**
	 * 将当前FSO对象拷贝到$to的位置。
	 * 如果目标位置已存在，则会抛出异常'复制目标已存在，无法复制'。
	 * @param $to 复制目标。可以是一个实际文件不存在的FSO对象，或者是一个字符串，表示复制目标的虚拟文件系统路径。
	 * @return 如果复制成功，返回一个FSO对象，表示复制的目标；如果复制失败，返回null。
	 */
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
				return $to;
			}else{
				throw new \jc\lang\Exception('this是IFile而to不是IFile，无法将一个文件复制成其它类型');
			}
		}else if ( $this instanceof IFolder ){
			if( $to instanceof IFolder ){
				throw new \jc\lang\Exception('暂时还没实现将一个目录递归复制到另一个位置');
			}else{
				throw new \jc\lang\Exception('this是IFolder而to不是IFolder，无法将一个目录复制成其它类型');
			}
		}else{
				throw new \jc\lang\Exception('this即不是IFile也不是IFolder');
		}
	}
	
	/**
	 * 将当前FSO对象移动到$to的位置。
	 * 如果目标位置已存在，则会抛出异常'复制目标已存在，无法复制'。
	 * @param $to 移动目标。可以是一个实际文件不存在的FSO对象，或者是一个字符串，表示移动目标的虚拟文件系统路径。
	 * @return 如果移动成功，返回一个FSO对象，表示移动的目标；如果移动失败，返回null。
	 */
	public function move($to){
		$ret = copy($to);
		$this->delete();
		return $ret;
	}
	
	
	public function property($sName)
	{
		return ($this->arrProperties and isset($this->arrProperties[$sName]))? $this->arrProperties[$sName]: null ; 
	}
	
	public function setProperty($sName,$value)
	{
		$this->arrProperties[$sName] = $value ;
	}
	
	private $sInnerPath = "" ;
	private $aFileSystem ;
	private $sName = "" ;
	private $sTitle = "" ;
	private $sExtname = "" ;
	
	private $sHttpUrl ;
	
	private $arrProperties ;
}
?>
