<?php
namespace org\jecat\framework\fs\vfs ;

class VFSWrapper
{
	public $context ;
	
	private $resource ;
	private $aFileSystem ;

	/**
	 * 根据 stream 协议 取得一个虚拟文件系统
	 */
	static public function vfs($sProtocol='vfs')
	{
		if( !isset(self::$arrProtocols[$sProtocol]) )
		{
			self::$arrProtocols[$sProtocol] = new VirtualFileSystem() ;
			//stream_wrapper_unregister($sProtocol) ;
			stream_wrapper_register($sProtocol,__CLASS__) ;
		}
		return self::$arrProtocols[$sProtocol] ;
	}

	static protected function findFileSystem($sUrl)
	{
		$arrUrlInfo = parse_url($sUrl) ;
		if( !$aVfs=self::vfs($arrUrlInfo['scheme']) )
		{
			return null ;
		}
	
		return $aVfs->fileSystem($arrUrlInfo['host'].$arrUrlInfo['path']) ;
	}
	static protected function localeFileSystem($sUrl)
	{
		$arrUrlInfo = parse_url($sUrl) ;
		if( !$aVfs=self::vfs($arrUrlInfo['scheme']) )
		{
			return null ;
		}
	
		return $aVfs->localeFileSystemPath($arrUrlInfo['host'].$arrUrlInfo['path']) ;
	}
	
	
	// -------------------------------------------
	// stream 的操作
	
	/**
	 * 仅仅做为 stream 操作的构造函数
	 * 非 stream 操作不会触发这个方法
	 */
	public function __construct()
	{}

	public function stream_open($sUrl,$sMode,$options,&$opened_path)
	{
		if( !list($this->aFileSystem,$sPath)=self::localeFileSystem($sUrl) )
		{
			return false ;
		}
		
		return ($this->resource=&$this->aFileSystem->openFile($sPath,$sMode,$options,$opened_path)) ? true: false ;
	}
	public function stream_close()
	{
		return $this->aFileSystem->closeFile($this->resource) ;
	}
	public function stream_eof()
	{
		return $this->aFileSystem->endOfFile($this->resource) ;
	}
	public function stream_lock($operation)
	{
		return $this->aFileSystem->lockFile($this->resource,$operation) ;
	}
	public function stream_flush()
	{
		return $this->aFileSystem->flushFile($this->resource) ;
	}
	public function stream_read($nCount)
	{
		return $this->aFileSystem->readFile($this->resource,$nCount) ;
	}
	public function stream_seek($offset,$whence=SEEK_SET)
	{
		return $this->aFileSystem->seekFile($this->resource,$offset,$whence) ;
	}
	public function stream_tell()
	{
		return $this->aFileSystem->tellFile($this->resource) ;
	}
	public function stream_write($data)
	{
		return $this->aFileSystem->writeFile($this->resource,$data) ; 
	}
	
	
// 	public function stream_cast (  $cast_as )
// 	{
		
// 	}
// 	public function stream_metadata (  $path ,  $option ,  $var )
// 	{
		
// 	}
// 	public function stream_set_option (  $option ,  $arg1 ,  $arg2 )
// 	{
		
// 	}
// 	public function stream_stat (  )
// 	{
		
// 	}
// 	public function stream_truncate (  $new_size )
// 	{
		
// 	}

	
	
	
	public function unlink ( $sUrl )
	{
		if( !list($this->aFileSystem,$sPath)=self::localeFileSystem($sUrl) )
		{
			return false ;
		}
		return $this->aFileSystem->unlinkFile($sPath) ;
	}
	public function url_stat ($sUrl,$flags)
	{
		if( !list($this->aFileSystem,$sPath)=self::localeFileSystem($sUrl) )
		{
			return false ;
		}
		return $this->aFileSystem->stat($sPath,$flags) ;
	}


	// -------------------------------------------
	// 非 stream 的操作（针对目录的操作）
	
	/**
	 * 目录操作的实际构造函数
	 */
	public function dir_opendir($sUrl,$options)
	{
		if( !list($this->aFileSystem,$sPath)=self::localeFileSystem($sUrl) )
		{
			return false ;
		}
		return ($this->resource=&$this->aFileSystem->opendir($sPath,$options)) ? true: false ;
	}
	public function dir_closedir()
	{
		return $this->aFileSystem->closedir($this->resource) ;
	}
	public function dir_readdir()
	{
		return $this->aFileSystem->readdir($this->resource) ;
	}
	public function dir_rewinddir ()
	{
		return $this->aFileSystem->rewinddir($this->resource) ;
	}
	
	public function mkdir($sUrl,$nMode,$options)
	{
		if( !list($aFileSystem,$sPath)=self::localeFileSystem($sUrl) )
		{
			return false ;
		}
		return $aFileSystem->mkdir($sPath,$nMode,$options) ;
	}
	/**
	 * 移动文件
	 */
	public function rename($sUrlFrom,$sUrlTo)
	{
		if( !list($aFromFs,$sFromPath)=self::localeFileSystem($sUrlFrom) )
		{
			return false ;
		}
		list($aToFs,$sToPath)=self::localeFileSystem($sUrlTo) ;
		
		// 同一文件系统内
		if( $aFromFs===$aToFs and $aToFs )
		{
			return $aFromFs->rename($sFromPath,$sToPath) ;
		}
		// 都是 本地文件系统
		else if( ($aFromFs instanceof LocalFileSystem) and ($aToFs instanceof LocalFileSystem) )
		{
			return rename( $aFromFs->url($sFromPath), $aToFs->url($sToPath) ) ;
		}
		// 暴力移动(对大文件会有性能问题)
		else
		{
			if( !file_put_contents($sUrlTo,file_get_contents($sUrlFrom)) )
			{
				return false ;
			}
			return $this->unlink($sUrlFrom) ;
		}
	}
	public function rmdir($sUrl,$options)
	{
		if( !list($aFileSystem,$sPath)=self::localeFileSystem($sUrl) )
		{
			return false ;
		}
		return $aFileSystem->rmdir($sPath,$options) ;		
	}

	static private $arrProtocols = array() ;
}
