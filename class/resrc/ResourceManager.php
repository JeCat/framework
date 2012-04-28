<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.7.1
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
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
	public function find($sFilename,$sNamespace='*',$bHttpUrl=false)
	{
		list($aFolder,$sFilename)=$this->findEx($sFilename,$sNamespace) ;
		if( !$sFilename )
		{
			return null ;
		}
		
		return $bHttpUrl ?
			$aFolder->httpUrl().'/'.$sFilename :
			new File($aFolder->path().'/'.$sFilename) ;
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
				if( file_exists($aFolder->path().'/'.$sFilename) )
				{
					return array($aFolder,$sFilename) ;
				}
			}
			else
			{
				foreach ($this->arrFilenameWrappers as $funcFilenameWrapper)
				{
					$sWrapedFilename = call_user_func_array($funcFilenameWrapper, array($sFilename)) ;
					
					if( file_exists($aFolder->path().'/'.$sWrapedFilename) )
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


