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
//  正在使用的这个版本是：0.8
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
namespace org\jecat\framework\fs ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\io\OutputStream;
use org\jecat\framework\io\InputStream;

class File extends FSO
{
	const CREATE_DEFAULT = 020664 ; 	// CREATE_RECURSE_DIR | 0664

	/**
	 * @return File
	 */
	static public function createInstance($sPath,$sClassName=null)
	{
		if( $sClassName===null or $sClassName===__CLASS__ )
		{
			return new self($sPath) ;
		}
		else
		{
			return parent::createInstance($sPath,$sClassName) ;
		}
	}
	
	/**
	 * @return io\IInputStream
	 */
	public function openReader()
	{
		$hHandle = fopen($this->path(),'r') ;
		if( !$hHandle )
		{
			return null ;
		}
		
		return InputStream::createInstance($hHandle) ;
	}
	
	/**
	 * @return io\IOutputStream
	 */
	public function openWriter($bAppend=false)
	{
		$sLocalPath = $this->path() ;
		
		if( !$this->makeParentFolder($sLocalPath,true) )
		{
			throw new Exception("无法创建文件所属目录：%s",$sLocalPath) ;
		}
		
		if( !$hHandle=fopen($sLocalPath,$bAppend?'a':'w') )
		{
			throw new Exception("无法打开文件：%s",$sLocalPath) ;
		}
		
		return OutputStream::createInstance($hHandle) ;
	}

	public function length()
	{
		return is_file($this->path())? filesize($this->path()): 0 ;
	}
	
	public function delete()
	{
		if( file_exists($sLocalPath=$this->path()) )
		{
			return unlink($sLocalPath) ;
		}
		
		else 
		{
			return false ;
		}
	}
	
	public function hash()
	{
		return md5($this->path()) ;
	}
	
	public function includeFile($bOnce=false,$bRequire=false)
	{
		if(!$bRequire)
		{
			if($bOnce)
			{
				return include_once $this->path() ;
			}
			else 
			{
				return include $this->path() ;
			}
		}
		
		else 
		{
			if($bOnce)
			{
				return require_once $this->path() ;
			}
			else 
			{
				return require $this->path() ;
			}
		}
	}
	
	private function makeParentFolder($sLocalPath,$bCreate=true)
	{
		$sLocalDirPath = dirname($sLocalPath) ;
		if( !is_dir($sLocalDirPath) )
		{
			if( $bCreate )
			{
				$nOldMark = umask(0) ;
				if( !mkdir($sLocalDirPath,Folder::CREATE_DEFAULT&0777,true) )
				{
					umask($nOldMark) ;
					return false ;
				}
				umask($nOldMark) ;
			}
			else
			{
				return false ;
			}
		}
		return true ;
	}

	public function create($nMode=Folder::CREATE_DEFAULT)
	{
		$sLocalPath = $this->path() ;
		
		if( !$this->makeParentFolder($sLocalPath,$nMode & Folder::CREATE_RECURSE_DIR) )
		{
			return false ;
		}
		
		if( !$hHandle=fopen($sLocalPath,'w') )
		{
			return false ;
		}
		
		fclose($hHandle) ;
		
		$nMask = $nMode&0777 ;
		if( $this->perms()!=$nMask and $this->canWrite() )
		{
			$nOldMark = umask(0) ;
			chmod( $sLocalPath, $nMask ) ;
			umask($nOldMark) ;
		}
		
		return true ;
	}

	public function exists()
	{
		return is_file($this->path());
	}
}

