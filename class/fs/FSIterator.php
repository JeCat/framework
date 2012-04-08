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
namespace org\jecat\framework\fs ;

abstract class FSIterator implements \Iterator{
	// 内容相关设定
	const CONTAIN_DOT = 0x01 ; // 包含'.'和'..'两个目录
	const CONTAIN_FILE = 0x02 ; // 包含文件
	const CONTAIN_FOLDER = 0x04 ; // 包含目录
	
	// 返回值相关设定
	const RETURN_FSO = 0x08 ; // 返回 FSO 对象，否则，返回字符串
	const RETURN_ABSOLUTE_PATH = 0x10 ; // 返回绝对路径，否则，返回相对路径
	
	// 递归相关设定
	const RECURSIVE_SEARCH = 0x20 ; // 递归搜索，否则，只搜索当前目录下
	const RECURSIVE_BREADTH_FIRST = 0x40 ; // 按广度优先进行搜索，否则，按深度优先进行搜索
	
	// 常用组合
	const DIR_ALL = 0x05 ; // CONTAIN_DOT | CONTAIN_FOLDER
	const FILE_AND_FOLDER = 0x06 ; // CONTAIN_FILE | CONTAIN_FOLDER
	const FILE = 0x02 ; // CONTAIN_FILE
	const FOLDER = 0x04 ; // CONTAIN_FOLDER
	
	// 默认值
	const FLAG_DEFAULT = 0x36 ; // CONTAIN_FILE | CONTAIN_FOLDER | RETURN_ABSOLUTE_PATH | RECURSIVE_SEARCH
	
	public function __construct(Folder $aParentFolder , $nFlags){
		$this->nFlags=$nFlags;
		$this->aParentFolder = $aParentFolder;
	}
	
	/**
	 * @return FSO
	 */
	abstract public function getFSO();
	
	/**
	 * @return string
	 */
	abstract public function absolutePath();
	
	/**
	 * @return string
	 */
	abstract public function relativePath();
	
	/**
	 * @return boolean
	 */
	abstract public function isFolder();
	
	/**
	 * @return boolean
	 */
	abstract public function isFile();
	
	protected $nFlags = self::FLAG_DEFAULT ;
	protected $aParentFolder = null ;
}


