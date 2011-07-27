<?php

namespace jc\fs ;

use jc\lang\Object;

class FSObject extends Object
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
		
		$arrFolders = explode('/', $sPath) ;
		
		
		$arrFoldersStack = array() ;
		foreach($arrFolders as $nIdx=>$sFolderName)
		{
			if( $sFolderName=='.' )
			{
				continue ;
			}
			
			if($sFolderName=='..')
			{
				$sParentFoldre = array_pop($arrFoldersStack) ;
				
				// windows 盘符
				if( preg_match("|^[a-z]:$|i",$sParentFoldre) )
				{
					// 放回去
					array_push($arrFoldersStack,$sFolderName) ;
				}
				
				continue ;
			}
			
			array_push($arrFoldersStack,$sFolderName) ;
		}
		
		return implode($sPathSeparator, $arrFoldersStack) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @var string
	 */
	private $sPath = "" ;
}
?>