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
namespace org\jecat\framework\setting ;

interface IKey extends \ArrayAccess
{
	/**
	 * 返回键的名称
	 * @return string
	 */
	public function name() ;
	
	/**
	 * 读取特定项的值
	 * @param string $sName 项的名字
	 * @param string $sDefault 项的默认值,如果项不存在，使用这个值
	 * @return mixed 项值
	 */
	public function item($sName='*',$sDefault=null) ;
	
	/**
	 * 设置一个项
	 * @param string $sName 项的名字
	 * @param mixed $value 项的值
	 */
	public function setItem($sName,$value) ;
	
	/**
	 * 是否存在项 
	 * @param string $sName 项的名字
	 * @return boolen 存在就返回true，不存在返回false
	 */
	public function hasItem($sName) ;
	
	/**
	 * 删除项 
	 * @param string $sName 项的名字
	 */
	public function deleteItem($sName) ;
	
	public function deleteKey() ;
	
	/**
	 * 获得所有项的名字的迭代器
	 * @return \Iterator 
	 */
	public function itemIterator() ;
	
	/**
	 * 获得所有键的名字的迭代器
	 * @return \Iterator 
	 */
	public function keyIterator() ;
	
	/**
	 * 保存所有键 
	 */
	public function save() ;
}


