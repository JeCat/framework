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
namespace org\jecat\framework\lang\oop ;

use org\jecat\framework\lang\Object;
use org\jecat\framework\fs\FSO;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\fs\Folder;

class Package extends Object implements \Serializable
{		
	const source = 1 ;
	const encode = 8 ;
	const compiled = 64 ;
	const nocompiled = 63 ;
	const all = 255 ;
	
	
	public function __construct($sNamespace,Folder $aFolder=null)
	{
		$this->setNamespace($sNamespace) ;
		
		$this->aFolder = $aFolder ;
		
		// OOXX.php
		$this->addClassFilenameWrapper(array(__CLASS__,'classFilenameWrapper')) ;		
		
		// OOXX.class.php
		// $this->addClassFilenameWrapper(function ($sClassName){ return "{$sClassName}.class.php" ; }) ;
		
		// class.OOXX.php
		// $this->addClassFilenameWrapper(function ($sClassName){ return "class.{$sClassName}.php" ; }) ;
	}
	
	static public function findFolder($sPath,$bAutoCreate=false)
	{
		if( !is_dir($sPath) and !$bAutoCreate )
		{
			throw new Exception(
					"注册 class package 时，提供的class源文件目录不存在：%s"
					, array($sPath)
			) ;
		}
		return new Folder($sPath) ;
	}

	/**
	 * @return org\jecat\framework\fs\Folder
	 */
	public function folder()
	{
		return $this->aFolder ;
	}
	
	public function setFolder(Folder $aFolder)
	{
		$this->aFolder = $aFolder ;
	}

	/**
	 * (namespace 是 php 的关键词不能做为函数名)
	 */
	public function ns()
	{
		return substr($this->sNamespace,0,-1) ;
	}
	
	protected function setNamespace($sNamespace)
	{
		if( substr($sNamespace,0,1)=='\\' )
		{
			if( strlen($sNamespace)>1 )
			{
				$sNamespace = substr($sNamespace,1) ;
			}
			else 
			{
				$sNamespace = '' ;
			}
		}
		
		$this->sNamespace = substr($sNamespace,-1)=='\\'? $sNamespace: ($sNamespace.'\\') ;
		$this->nNamespaceLen = strlen($this->sNamespace) ;
	}

	public function searchClass($sClassName)
	{
		if(!$this->aFolder)
		{
			return null ;
		}
		
		if( list($sInnerFolderPath,$sClassName) = $this->parsePath($sClassName) )
		{
			return $this->searchClassEx($sInnerFolderPath,$sClassName) ;
		}
		else
		{
			return null ;
		}
	}
	
	public function searchClassEx($sSubFolder,$sShortClassName)
	{
		if(!$this->aFolder)
		{
			return null ;
		}
		
		foreach($this->arrClassFilenameWraps as $func)
		{
			$sClassFilename = call_user_func_array($func, array($sShortClassName)) ;
			
			$sClassFilePath = $sSubFolder? ($sSubFolder . '/' . $sClassFilename): $sClassFilename ;
			
			if( $sFilepath=$this->aFolder->find($sClassFilePath,FSO::file|Folder::FIND_RETURN_PATH) )
			{
				return $sFilepath ;
			}
		}
		
		return null ;
	}
	
	public function parsePath($sClassName)
	{
		if( $this->nNamespaceLen===1 and $this->sNamespace==='\\' )
		{
			$sPath = $sClassName ;
		}
		
		else if( substr($sClassName,0,$this->nNamespaceLen)===$this->sNamespace )
		{
			$sPath = substr($sClassName,$this->nNamespaceLen) ;
		}
		
		else
		{
			return null ;
		}
		
		$pos = strrpos($sPath,'\\') ;
		if( $pos!==false )
		{
			return array(
				str_replace('\\', '/', substr($sPath,0,$pos))
				, substr($sPath,$pos+1)
			) ;
		}
		else 
		{
			return array('',$sPath) ;
		}
	}

	public function addClassFilenameWrapper($func) 
	{
		$this->arrClassFilenameWraps[] = $func ;
	}
	
	public function createClassFile($sInnerPath,$sShortClassName)
	{
		if($this->aFolder)
		{
			if( $fnClassFilenameWraps = reset($this->arrClassFilenameWraps) )
			{
				$sClassPath = call_user_func_array($fnClassFilenameWraps, array($sShortClassName)) ;
			}
			else 
			{
				$sClassPath = $sShortClassName . '.php' ;
			}
			if($sInnerPath)
			{
				$sClassPath = $sInnerPath.'/'.$sClassPath ;
			}
			
			if( !$aClassFile=$this->aFolder->createChildFile($sClassPath) )
			{
				throw new Exception(
					"无法在以下路径上创建类%s的编译文件：%s",array($sClassFullName,$sClassCompilePath)
				) ;
			}
			
			return $aClassFile ;
		}
		
		else
		{
			return null ;
		}
	}
	
	/**
	 * @return \Iterator
	 */
	public function classIterator($sSubNamespace=null)
	{
		return new PackageClassIterator($this,$sSubNamespace) ;
	}
	
	static public function classFilenameWrapper($sClassName)
	{
		return $sClassName.'.php' ;
	}
	
	public function serialize()
	{
		$arrData = array(
				'sNamespace' => &$this->sNamespace ,
				// 'nNamespaceLen' => &$this->nNamespaceLen ,
				'aFolder' => $this->aFolder ,
		) ;
		return serialize($arrData) ;
	}
	
	public function unserialize($serialized)
	{
		$arrData = unserialize($serialized) ;
		
		$this->__construct($arrData['sNamespace'],$arrData['aFolder']) ;
	}
	
	public function signature()
	{
		return md5( $this->sNamespace . ':' . $this->aFolder->path() ) ;
	}
	
	private $sNamespace ;
	
	private $nNamespaceLen = 0 ;
	
	private $aFolder ;
	
	private $arrClassFilenameWraps = array() ;
}

