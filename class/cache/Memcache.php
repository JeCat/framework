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


class Memcache extends Cache
{
	public function __construct($sServer='127.0.0.1',$nPort=11211)
	{
		if( !function_exists('memcache_connect') )
		{
			throw new \Exception('not fount memcache functions .') ;
		}
		
		if( !$this->hMemcacheConnection = memcache_connect($sServer,$nPort) )
		{
			throw new \Exception('can not connect memcache server: '.$sServer .':'. $nPort) ;
		}
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	public function item($sDataName)
	{
		$data = memcache_get($this->hMemcacheConnection,$sDataName) ;
		return unserialize($data) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function setItem($sName,$data,$nExpire=self::expire_default)
	{
		$data = serialize($data) ;
		return memcache_set($this->hMemcacheConnection,$sName,$data,0,$nExpire=self::expire_default) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function delete($sDataName)
	{
		return memcache_delete($this->hMemcacheConnection,$sDataName) ;
	}

	/**
	 * Enter description here ...
	 *
	 * @return bool
	 */
	public function clear()
	{
		return true ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function isExpire($sDataName)
	{
		return $this->get($sDataName)===null ;
	}
	
	
	private $hMemcacheConnection ;
}

