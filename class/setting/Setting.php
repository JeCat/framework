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
namespace org\jecat\framework\setting;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;

/**
 * @wiki /配置
 * 
 * === Key 和 Item ===
 * 系统的配置信息保存在 org\framework\setting\Setting 对像中。
 * 
 * 配置信息存储在[b]配置项（item）[/b]中，每个配置项保存一项数据。数据可以是基本数据类型（字符串、整数、布尔等），也可以是复合数据结构（数组、对象）
 * 
 * [b]配置键（key）[/b]负责维护[b]配置项（item）[/b]，一个key可以提供多个 item 和 多个下级 key。
 * 
 * 系统所需的配置信息依据自身的业务关系和职能分类，分别保存在各个不同的[b]配置键（key）[/b]里，这些[b]配置键[/b]以树状结构存放。
 * 
 * key 和 item 很像文件系统中的目录和文件：每个key可以拥有多个item和下级key；数据是保存在item中的；key负责组织收纳各个item和下级key。
 * 
 * === 访问配置信息 ===
 * 通过 org\framework\setting\Setting 类的单例对象访问所有的 key 和 item 。
 * 访问配置信息时需要像 Setting 对象提供 key路径 和 item名称，Setting对象返回保存在 item 中的数据。
 * 
 */
abstract class Setting extends Object implements ISetting
{	
	public function saveKey($sPath)
	{
		if (! $aKey = $this->key ( $sPath ))
		{
			return;
		}
		$aKey->save ();
	}
	
	/**
	 * @return \Iterator 
	 */
	public function keyIterator($sPath)
	{
		$aKey = $this->key ( $sPath );
		
		if (! $aKey)
		{
			return new \EmptyIterator ();
		}
		
		return $aKey->keyIterator ();
	}
	
	/**
	 * @return \Iterator 
	 */
	public function itemIterator($sPath)
	{
		$aKey = $this->key ( $sPath );
		
		if (! $aKey)
		{
			return new \EmptyIterator ();
		}
		
		return $aKey->itemIterator ();
	}
	
	public function item($sPath,$sName='*',$defaultValue=null)
	{
		if (!$aKey=$this->key($sPath,$defaultValue!==null))
		{
			return null;
		}
		return $aKey->item($sName,$defaultValue) ;
	}
	
	public function setItem($sPath, $sName, $value)
	{
		if (! $aKey = $this->key ( $sPath ))
		{
			if( !$aKey=$this->createKey($sPath) )
			{
				throw new Exception("无法保存配置建：%s",$sPath) ;
			}
		}
		$aKey->setItem ( $sName, $value );
	}
	
	public function hasItem($sPath, $sName)
	{
		if (! $aKey = $this->key ( $sPath ))
		{
			return null;
		}
		return $aKey->hasItem ( $sName );
	}
	
	public function deleteItem($sPath, $sName)
	{
		if (! $aKey = $this->key ( $sPath ))
		{
			return;
		}
		$aKey->deleteItem ( $sName );
	}
	
	public function deleteKey($sPath)
	{
		if( $aKey = $this->key($sPath,false) )
		{
			foreach($this->keyIterator($sPath) as $aSubKey)
			{
				$aSubKey->deleteKey() ;
			}
	
			$aKey->deleteKey() ;
		}
	}
}
