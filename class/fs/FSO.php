<?php
namespace org\jecat\framework\fs ;

use org\jecat\framework\lang\Object;

abstract class FSO extends Object implements \Serializable
{
	const file =	0100000 ;
	const folder = 0200000 ;
	const unknow = 0 ;
	const type = 0300000 ;
	
	const CLEAN_PATH = 0400000 ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function __construct($sPath,$nFlag=0)
	{
		$this->sPath = ($nFlag&self::CLEAN_PATH)? $sPath: self::tidyPath($sPath) ;
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
	public function delete($bRecurse=false,$bIgnoreError=false)
	{
		// return $this->fileSystem()->delete($this->path(),$bRecurse,$bIgnoreError) ;
	}
	
	/**
	 * @return Folder
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
	
	public function isCaseSensitive()
	{
		return $this->bCaseSensitive ;
	}
	
	public function setCaseSensitive($bCaseSensitive=true)
	{
		return $this->bCaseSensitive = $bCaseSensitive ;
	}
	
	/**
	 * 整理路径，清理路径中出现的 .. 和 .
	 */
	static public function tidyPath(& $sPath)
	{
			if(!is_string($sPath))
			{
				debug_print_backtrace() ;
				exit() ;
			}
		// 统一、合并斜线
		$sPath = preg_replace('|[/\\\\]+|', '/', $sPath) ;
	
		// 处理 .. , .
		if( preg_match('`[/^](..|.)[/$]`',$sPath) )
		{
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
		
			$sPath = implode('/', $arrFoldersStack) ;
		}
		
		return $sPath ;
	}
	
	/**
	 * 格式化路径，清理路径中重复的斜线，删除路径末尾的 / ,补充路径开头的 /
	 */
	static public function formatPath(& $sPath,$bAbs=true)
	{
		if(!$sPath)
		{
			return '' ;
		}
		
		// 统一、合并斜线
		$sPath = preg_replace('|[/\\\\]+|', '/', $sPath) ;
	
		// 补充开头的 /
		if( $bAbs and substr($sPath,0,1)!='/' ) 
		{
			$sPath = '/'.$sPath ;
		}
		
		// 清理末尾的斜线
		if( substr($sPath,-1)=='/' ) 
		{
			$sPath = substr($sPath,0,-1) ;
		}
		
		return $sPath ;
	}
	
	/**
	 * Enter description here ...
	 *
	 * @return bool
	 */
	public function canRead()
	{
		return is_readable($this->sPath) ;
	}
	
	/**
	 * Enter description here ...
	 *
	 * @return bool
	 */
	public function canWrite()
	{
		return is_writeable($this->sPath) ;
	}
	
	/**
	 * Enter description here ...
	 *
	 * @return bool
	 */
	public function canExecute()
	{
		return is_executable($this->sPath) ;
	}
	
	/**
	 * Enter description here ...
	 *
	 * @return int
	 */
	public function perms()
	{
		return fileperms($this->sPath) ;
	}
	
	/**
	 * Enter description here ...
	 *
	 * @return bool
	 */
	public function setPerms($nMode)
	{
		$nOldMark = umask(0) ;
		$bRes = chmod($this->sPath,$nMode) ;
		umask($nOldMark) ;
	
		return $bRes ;
	}
	
	public function createTime()
	{
		return filectime($this->sPath) ;
	}
	
	public function modifyTime()
	{
		return filemtime($this->sPath) ;
	}
	
	public function accessTime()
	{
		return fileatime($this->sPath) ;
	}
	
	public function isHidden()
	{
		return false ;
	}
	
	/**
	 * 返回能够通过http访问该文件对象的url——如果该文将对象可以在http上被访问的话
	 */
	public function httpUrl()
	{
		return $this->sHttpUrl ;
	}
	
	public function setHttpUrl($sHttpUrl)
	{
		$this->sHttpUrl = $sHttpUrl ;
	}
	
	/**
	 * 计算两个路径之间的相对路径
	 * in : FSO object or string
	 * return : string
	 */
	static public function relativePath($sFromPath,$sToPath)
	{
		if($sFromPath instanceof IFSO){
			$sFromPath = $sFromPath->path();
		}
		if($sToPath instanceof IFSO){
			$sToPath = $sToPath->path();
		}
		
		if( substr($sFromPath,0,1)!=='/' or substr($sFromPath,1,1)!==':' )
		{
			$sFromPath = getcwd() .'/' . $sFromPath ;
		}
		if( substr($sToPath,0,1)!=='/' or substr($sToPath,1,1)!==':' )
		{
			$sToPath = getcwd() .'/' . $sToPath ;
		}
		
		// 大致算法就是:  根据‘/’把路径拆分放进数组，然后从第一个开始比较，相同的忽略掉，直到遇到不同的为止。
		//拆分路径放进数组:
		$arrFromPath = explode('/', $sFromPath);
		$arrToPath = explode('/', $sToPath);
	
		//开始比对数组，存下不同的部分:
		$remainFromPath = array_diff($arrFromPath, $arrToPath);
		$remainToPath = array_diff($arrToPath, $arrFromPath);
	
		//算出$a路径的剩余深度
		$count = count($remainFromPath);
	
		//算出$b剩余路径，再合并成路径形式:
		$relative_ToPath = join('/', $remainToPath);
	
		$new = '';
		//计算相对路径前缀
		for($i = 0; $i < $count-1; $i++)
		{
			$new .= '../';
		}
		$_path = $new . $relative_ToPath;
		return $_path;
	}
	
	public function serialize()
	{
		$arrData = array(
				'sPath' =>& $this->sPath ,
				'sName' =>& $this->sName ,
				'sTitle' =>& $this->sTitle ,
				'sExtname' =>& $this->sExtname ,
				'bCaseSensitive' =>& $this->bCaseSensitive ,
				'sHttpUrl' =>& $this->sHttpUrl ,
		) ;	
		return serialize($arrData) ;
	}
	
	public function unserialize($serialized)
	{	
		$arrData = unserialize($serialized) ;
		
		$this->sPath =& $arrData['sPath'] ;
		$this->sName =& $arrData['sName'] ;
		$this->sTitle =& $arrData['sTitle'] ;
		$this->sExtname =& $arrData['sExtname'] ;
		$this->bCaseSensitive =& $arrData['bCaseSensitive'] ;
		$this->sHttpUrl =& $arrData['sHttpUrl'] ;
	}
	
	private $sPath ;
	private $sName ;
	private $sTitle ;
	private $sExtname ;
	
	private $bCaseSensitive = true ;
	
	private $sHttpUrl ;
	
}
?>
