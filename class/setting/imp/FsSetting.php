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
namespace org\jecat\framework\setting\imp;

use org\jecat\framework\fs\FSO;
use org\jecat\framework\fs\Folder;
use org\jecat\framework\setting\Setting;

class FsSetting extends Setting implements \Serializable
{
	/**
	 * 
	 * @param Folder $aRootFolder
	 */
	public function __construct(Folder $aRootFolder)
	{
		$this->aRootFolder = $aRootFolder;
	}
	
	static public function createFromPath($sFolderPath) 
	{
		return new self( Folder::createFolder($sFolderPath) ) ;
	} 

	/**
	 * @return IKey 
	 */
	public function key($sPath,$bAutoCreate=false)
	{
		$sKeyPath = self::transPath($sPath,false) ;
		$sFlyweightKey = $this->aRootFolder->path() . '/' . $sKeyPath ;
		
		if( !$aKey=FsKey::flyweight($sFlyweightKey,false) )
		{
			if( !$aFolder=$this->aRootFolder->findFolder($sKeyPath,$bAutoCreate?Folder::FIND_AUTO_CREATE:0) )
			{
				return null ;
			}
			$aKey = new FsKey($aFolder) ;
			FsKey::setFlyweight($aKey,$sFlyweightKey) ;
		}
		
		return $aKey ;
	}
	
	public function createKey($sPath)
	{
		return $this->key($sPath,true) ;
	}
	
	public function hasKey($sPath)
	{
		return $this->aRootFolder->findFile(self::transPath($sPath))? true: false ;
	}
	
	/**
	 * @return \Iterator 
	 */
	public function keyIterator($sPath)
	{
		if ( !$aKey=$this->key($sPath) )
		{
			return new \EmptyIterator ();
		}
		return $aKey->keyIterator ();
	}
	
	static public function transPath($sPath,$bItemsPath=true)
	{
		// 去掉开头的 '/'
		if ( substr($sPath,0,1)=='/' )
		{
			$sPath = strlen($sPath)>1? substr($sPath,1): '' ;
		}
		
		// items.php
		if($bItemsPath)
		{
			if($sPath)
			{
				$sPath.= '/' ;
			}
			
			$sPath.= FsKey::itemFilename ;
		}
		
		return $sPath ;
	}
	
	/**
	 * 在指定的路径上，分离出一个setting
	 * @param string $sPath 键路径
	 * @return ISetting
	 */
	public function separate($sPath)
	{
		$sPath = self::transPath($sPath,false) ;
		$aNewSettingFolder = $this->aRootFolder->findFolder($sPath,Folder::FIND_AUTO_CREATE) ;
		return new self($aNewSettingFolder) ;
	}
	
	public function serialize ()
	{
		return FSO::tidyPath($this->aRootFolder->path()) ;
	}

	/**
	 * @param serialized
	 */
	public function unserialize ($serialized)
	{
		$this->aRootFolder = new Folder($serialized,Folder::FIND_AUTO_CREATE) ;
	}
	
	/**
	 * @var org\jecat\framework\fs\Folder
	 */
	private $aRootFolder;
}


