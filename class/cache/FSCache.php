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
namespace org\jecat\framework\cache ;

use org\jecat\framework\fs\Folder;

class FSCache extends Cache
{
	public function __construct($sFolder)
	{
		$this->aFolder = new Folder($sFolder) ;
		if( !$this->aFolder->exists() )
		{
			$this->aFolder->create() ;
		}
	}
	
	public function item($sDataPath)
	{
		$nExpireTime = $this->expireTime($sDataPath) ;
		if( $nExpireTime<0 )
		{
			return null ;
		}
		else if( $nExpireTime>0 and $nExpireTime<time() )
		{
			$this->delete($sDataPath) ;
			return null ;
		}
		
		// 尝试 .php
		if( $sFilePath = $this->aFolder->findFile($sDataPath.'.php',Folder::FIND_RETURN_PATH) )
		{
			return include $sFilePath ;
		}
		// 尝试 .data
		if( $sFilePath = $this->aFolder->findFile($sDataPath.'.data',Folder::FIND_RETURN_PATH) )
		{
			return unserialize(file_get_contents($sFilePath)) ;
		}

		return null ;
	}
	
	public function setItem($sDataPath,$data,$nExpire=self::expire_default,$fCreateTimeMicroSec=-1)
	{
		if(is_object($data))
		{
			$sSerialize = serialize($data) ;
			$sFilePath = $sDataPath.'.data' ;
		}
		else
		{
			$sSerialize = "<?php
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
/*-- Project Introduce --*/\r\nreturn ".var_export($data,true).' ;' ;
			$sFilePath = $sDataPath.'.php' ;
		}
		if( !$sFilePath=$this->aFolder->findFile($sFilePath,Folder::FIND_AUTO_CREATE|Folder::FIND_RETURN_PATH) )
		{
			return false ;
		}
		file_put_contents( $sFilePath,$sSerialize) ;
		
		// create time
		if($fCreateTimeMicroSec<0)
		{
			$fCreateTimeMicroSec = microtime(true) ;
		}
		
		// expire time
		if( $nExpire===Cache::expire_allways )
		{
			$nExpireSec = 0 ;
		}
		else if( $nExpire===Cache::$expire_default )
		{
			$nExpireSec = ceil($fCreateTimeMicroSec) + Cache::$expire_default ;
		}
		else
		{
			$nExpireSec = ceil($fCreateTimeMicroSec) + $nExpire ;
		}
		
		if( !$sFilePath=$this->aFolder->findFile($sDataPath.'.time',Folder::FIND_AUTO_CREATE|Folder::FIND_RETURN_PATH) )
		{
			return false ;
		}

		file_put_contents( $sFilePath.'.time', "<?php
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
/*-- Project Introduce --*/ return array({$fCreateTimeMicroSec},{$nExpireSec}) ;" ) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 */
	public function delete($sDataPath)
	{
		// 所有
		if( empty($sDataPath) )
		{
			$this->aFolder->delete(true,true) ;
		}
		// 删除目录
		else if( $aFolder=$this->aFolder->findFolder($sDataPath) )
		{
			$aFolder->delete(true,true) ;
		}
		// 删除指定内容
		else
		{
			$this->aFolder->deleteChild($sDataPath.'.data') ;
			$this->aFolder->deleteChild($sDataPath.'.php') ;
			$this->aFolder->deleteChild($sDataPath.'.time') ;
		}
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function isExpire($sDataPath)
	{
		if( !$sFilePath = $this->aFolder->findFile($sDataPath.'.time',Folder::FIND_RETURN_PATH) )
		{
			return true ;
		}

		list(,$nExpireTime) = include $sFilePath ;
		return $nExpireTime>0 and $nExpireTime<time() ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return float
	 */
	public function createTime($sDataPath)
	{
		if( !$sFilePath = $this->aFolder->findFile($sDataPath.'.time',Folder::FIND_RETURN_PATH) )
		{
			return 0 ;
		}

		list($fCreateTime,) = include $sFilePath ;
		return (float)$fCreateTime ;
	}

	/**
	 * Enter description here ...
	 *
	 * @return int
	 */
	public function expireTime($sDataPath)
	{
		if( !$sFilePath = $this->aFolder->findFile($sDataPath.'.time',Folder::FIND_RETURN_PATH) )
		{
			return -1 ;
		}
	
		list(,$nExpireTime) = include $sFilePath ;
		return (int)$nExpireTime ;
	}
	
	/**
	 * @var org\jecat\framework\fs\FsFolder
	 */
	private $aFolder ;
}

