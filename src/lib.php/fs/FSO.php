<?php

namespace jc\fs ;

use jc\lang\Object;

class FSO extends Object
{
	const PATH_STYLE_WIN = '\\' ;
	const PATH_STYLE_UNIX = '/' ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function __construct($sPath='')
	{
		$this->sPath = $sPath ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return FSO
	 */
	static public function createFSO($sPath)
	{
		if(is_file($sPath))
		{
			return new File($sPath) ;
		}
		else if(is_dir($sPath))
		{
			return new Dir($sPath) ;
		}
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	public function path()
	{
		return $this->sPath ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	public function name()
	{
		return basename($this->path()) ;
	}

	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	public function parentPath()
	{
		return dirname($this->path()).'/' ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function exists()
	{
		file_exists($this->path()) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	public function lastModified()
	{
		filemtime($this->path()) ;
	}

	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function canRead()
	{
		return is_readable($this->path()) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function canWrite()
	{
		return is_writeable($this->path()) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function canExecute()
	{
		return is_executable($this->path()) ;
	}

	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	public function perms()
	{
		return fileperms($this->path()) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function setPerms($nMode)
	{
		return chmod($this->path(),$nMode) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function delete()
	{
		unlink($this->path()) ;
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
	 * 当前文件系统，是否对文件名的大小写敏感
	 *
	 * @access	public
	 * @static
	 * @return	bool
	 */
	static public function isFileSystemCaseSensitive()
	{
		return strtolower(substr(PHP_OS,0,3))!='win' ;
	}

	static public function formatPath($sPath,$sPathSeparator=DIRECTORY_SEPARATOR)
	{
		// 统一、合并斜线
		$sPath = preg_replace('|[/\\\\]+|', '/', $sPath) ;
		
		$arrFoldersStack = explode('/', $sPath) ;
		$arrFoldersStack = array_reverse($arrFoldersStack) ;
		
		// 第一次遍历先处理 "."
		$nLen = count($arrFoldersStack) ;
		for($i=0;$i<$nLen;$i++)
		{
			if($arrFoldersStack[$i]=='.')
			{
				unset($arrFoldersStack[$i]) ; 
			}
		}
		$arrFoldersStack = array_values($arrFoldersStack) ;
	
		// 第二次遍历处理 ".."
		$nLen = count($arrFoldersStack) ;
		for($i=0;$i<$nLen;$i++)
		{
			if($arrFoldersStack[$i]=='..')
			{				
				unset($arrFoldersStack[$i]) ;
				
				// 移除上一级目录，但是以下情况例外：
				// windows 路径风格  c:\..\xxx
				// 或 unix 路径风格  /../xxx	
				if( $i<$nLen-2 )
				{
					unset($arrFoldersStack[$i+1]) ;
					$i ++ ;
				}
			}
		}
		
		// 
		$arrFoldersStack = array_reverse($arrFoldersStack) ;
		$sNewPath = implode($sPathSeparator, $arrFoldersStack) ;
		
		// 转换 windows下  c:\xxx 这种情况
		$sNewPath = preg_replace("|^([a-z]):/|i","\\1:\\",$sNewPath) ;
		
		return $sNewPath ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @var string
	 */
	private $sPath = "" ;
}
?>