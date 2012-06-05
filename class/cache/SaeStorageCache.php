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
namespace org\jecat\framework\cache ;

use org\jecat\framework\lang\Object;

class SaeStorageCache extends Cache
{
	public function __construct($sDomain,$sFolder='')
	{
		$this->sDomain = $sDomain ;
		$this->sFolderPrefix = trim($sFolder,'/').'/' ;
		$this->aSaeStorage = Object::singleton('SaeStorage') ;
	}
	
	public function item($sDataPath)
	{
		$sDataPath = trim($sDataPath,'/') ;
		$nExpireTime = $this->expireTime($sDataPath) ;
		if( $nExpireTime<=0 )
		{
			return null ;
		}
		else if( $nExpireTime>0 and $nExpireTime<time() )
		{
			$this->delete($sDataPath) ;
			return null ;
		}
		
		return unserialize($this->aSaeStorage->read($this->sDomain,$this->sFolderPrefix.$sDataPath.'.data',$sSerialize)) ;
	}
	
	public function setItem($sDataPath,$data,$nExpire=self::expire_default,$fCreateTimeMicroSec=-1)
	{
		$sDataPath = trim($sDataPath,'/') ;
		$sSerialize = serialize($data) ;
		$sFilePath = $this->sFolderPrefix.$sDataPath.'.data' ;
			
		if( !$this->aSaeStorage->write($this->sDomain,$sFilePath,$sSerialize) )
		{
			return false ;
		}
		
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
		
		return $this->aSaeStorage->write($this->sDomain,$this->sFolderPrefix.$sDataPath.'.time',$sSerialize) ;
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
			$this->clear() ;
		}
		// 删除指定内容
		else
		{
			$sDataPath = trim($sDataPath,'/') ;
			$this->aSaeStorage->delete($this->sDomain,$this->sFolderPrefix.$sDataPath.'.data') ;
			$this->aSaeStorage->delete($this->sDomain,$this->sFolderPrefix.$sDataPath.'.time') ;
			$this->aSaeStorage->deleteFolder($this->sDomain,$this->sFolderPrefix.$sDataPath) ;
		}
	}

	/**
	 * Enter description here ...
	 *
	 * @return bool
	 */
	public function clear()
	{
		return $this->aSaeStorage->deleteFolder($this->sDomain,$this->sFolderPrefix) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function isExpire($sDataPath)
	{
		$nExpireTime = $this->expireTime($sDataPath) ;
		return $nExpireTime>0 and $nExpireTime<time() ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return float
	 */
	public function createTime($sDataPath)
	{
		$data = $this->aSaeStorage->read($this->sDomain,$this->sFolderPrefix.ltrim($sDataPath,'/').'.time') ;
		if($data===false)
		{
			return 0 ;
		}
		list($fCreateTime,) = explode(',',$data) ;
		return (int)$fCreateTime ;
	}

	/**
	 * Enter description here ...
	 *
	 * @return int
	 */
	public function expireTime($sDataPath)
	{
		$data = $this->aSaeStorage->read($this->sDomain,$this->sFolderPrefix.ltrim($sDataPath,'/').'.time') ;
		if($data===false)
		{
			return 0 ;
		}
		list(,$nExpireTime) = explode(',',$data) ;
		return (int)$nExpireTime ;
	}

	private $sDomain ;
	private $aSaeStorage ;
}

