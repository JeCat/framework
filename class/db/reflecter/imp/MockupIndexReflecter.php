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
namespace org\jecat\framework\db\reflecter\imp;

use org\jecat\framework\db\reflecter\AbStractIndexReflecter;

class MockupIndexReflecter extends AbStractIndexReflecter
{
	
	function __construct($aDBReflecterFactory, $sTable, $sIndexName, $sDBName = null)
	{
		$this->sName = $sIndexName;
	}
	
	public function isPrimary()
	{
		if (! isset ( $this->arrMetainfo ['isPrimary'] ))
		{
			return null;
		}
		return $this->arrMetainfo ['isPrimary'];
	}
	
	public function isUnique()
	{
		if (! isset ( $this->arrMetainfo ['isUnique'] ))
		{
			return null;
		}
		return $this->arrMetainfo ['isUnique'];
	}
	
	public function isFullText()
	{
		if (! isset ( $this->arrMetainfo ['isFullText'] ))
		{
			return null;
		}
		return $this->arrMetainfo ['isFullText'];
	}
	
	/**
	 * 索引是否存在(有效)
	 * @return boolen 如果存在返回true 如果不存在返回false 
	 */
	public function isExist()
	{
		return $this->bIsExist;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @return array
	 */
	public function columnNames()
	{
		if (! isset ( $this->arrMetainfo ['columns'] ))
		{
			return null;
		}
		return $this->arrMetainfo ['columns'];
	}
	
	public function name()
	{
		return $this->sName;
	}
	
	public $arrMetainfo = array ();
	public $bIsExist = false;
	
	public $sDBName;
	public $sTable;
	public $sName;

}
