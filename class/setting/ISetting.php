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
namespace org\jecat\framework\setting ;

interface ISetting
{
	/**
	 * 获得一个键对象
	 * @param string $sPath 键路径
	 * @return IKey 
	 */
	public function key($sPath,$bAutoCreate=false) ;
	
	/**
	 * 新建一个键
	 * @param string $sPath 键路径
	 * @return IKey 
	 */
	public function createKey($sPath) ;
	
	/**
	 * 检查是否存在键 
	 * @param string $sPath 键路径
	 * @return boolen 如果存在返回true ,不存在返回false
	 */
	public function hasKey($sPath) ;
	
	/**
	 * 删除一个键
	 * @param string $sPath 键路径
	 * @return boolen 删除成功返回true，失败返回false
	 */
	public function deleteKey($sPath) ;
	
	/**
	 * 保存键
	 * @param string $sPath 键路径
	 */
	public function saveKey($sPath) ;
	
	/**
	 * 获得子键的键名迭代器
	 * @param string $sPath 键路径
	 * @return \Iterator 
	 */
	public function keyIterator($sPath) ;
	
	/**
	 * 获得项的值
	 * @param string $sPath 键路径
	 * @param string $sName 项名
	 * @param mixed $defaultValue 默认值 ,如果项不存在就取默认值,并且以默认值新建项
	 */
	public function item($sPath,$sName='*',$defaultValue=null) ;
	
	/**
	 * 设置项的值
	 * @param string $sPath 键路径
	 * @param string $sName 项名
	 * @param mixed $value
	 */
	public function setItem($sPath,$sName,$value) ;
	
	/**
	 * 检查项是否存在
	 * @param string $sPath 键路径
	 * @param string $sName 项名
	 * @return boolen 如果项存在就返回true,如果不存在返回false
	 */
	public function hasItem($sPath,$sName) ;
	
	/**
	 * 删除项 
	 * @param string $sPath 键路径
	 * @param string $sName 项名
	 */
	public function deleteItem($sPath,$sName) ;
	
	/**
	 * 获得项的名字迭代器
	 * @param string $sPath 键路径
	 * @return \Iterator 
	 */
	public function itemIterator($sPath) ;
	
	/**
	 * 在指定的路径上，分离出一个setting
	 * @param string $sPath 键路径
	 * @return ISetting
	 */
	public function separate($sPath) ;
}


