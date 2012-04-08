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
namespace org\jecat\framework\util\match ;

use org\jecat\framework\util\HashTable;

class ResultSet extends HashTable
{
	/**
	 * 返回第一项匹配结果
	 * 通常用户不需要遍历匹配结果集的情况
	 * 如果结果集为空返回 false ，可以用判断是否匹配
	 * 
	 * @return Result
	 */
	public function result()
	{
		if( !$this->count() )
		{
			return false ;
		}
		
		else 
		{
			return $this->rewind() ;
		}
	}

	public function content($nGrp=0) 
	{
		return $this->count()? $this->rewind()->content($nGrp): null ;
	}
	
	public function position($nGrp=0) 
	{
		return $this->count()? $this->rewind()->position($nGrp): null ;
	}
	
	public function length($nGrp=0)
	{
		return $this->count()? $this->rewind()->length($nGrp): null ;
	}
	
	public function add(Result $aResult)
	{
		parent::add($aResult) ;
	}
	
	
	
}
