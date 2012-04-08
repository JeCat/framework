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
namespace org\jecat\framework\session ;

use org\jecat\framework\lang\Exception;

class OriginalSession extends Session
{
	public function __construct()
	{
		if( self::singleton(false) )
		{
			throw new Exception("OriginalSession 类只能在单件模式下工作，OriginalSession类已经创建，无法重复创建该类的实例。") ;
		}
		
		self::setSingleton($this) ;
		
		register_shutdown_function(function (){
			session_write_close() ;
		}) ;
	}
	
	public function sessionId()
	{
		return session_id() ;
	}
	
	public function setSessionId($sId)
	{
		session_id($sId) ;
	}
	
	public function & variable($sName)
	{
		return $_SESSION[$sName] ;
	}

	public function addVariable($sName,& $var)
	{
		$_SESSION[$sName] =& $var ;
	}
	
	public function hasVariable($sName)
	{
		return array_key_exists($sName, $_SESSION) ;
	}

	public function removeVariable($sName)
	{
		unset($_SESSION[$sName]) ;
	}
	
	public function clear()
	{
		session_unset() ;
	}
	
	/**
	 * @return org\jecat\framework\pattern\iterate\INonlinearIterator
	 */
	public function variableNameIterator()
	{
		return new \org\jecat\framework\pattern\iterate\ArrayIterator( array_keys($_SESSION) ) ;
	}
	
	/**
	 * 将session中的数据保存到实际设备中
	 */
	public function commit()
	{
		session_commit() ;
	}
}
