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
namespace org\jecat\framework\mvc\controller ;

use org\jecat\framework\util\IDataSrc;
use org\jecat\framework\util\IFilterMangeger;
use org\jecat\framework\util\DataSrc;

class Request extends DataSrc
{
	public function get($sName,$default=null)
	{
		$value = parent::get($sName) ;
		
		// 尝试转换 xxx.ooo 为 xxx_ooo
		if( $value===null and strpos($sName,'.')!==false )
		{
			$value = parent::get(str_replace('.','_',$sName)) ;
		}
		
		// 使用默认值
		if( $value===null and $default!==null )
		{
			$value = $default ;
			$this->set($sName,$value) ;
		}
		
		return $value ;
	}
	
	public function has($sName)
	{
		if( !parent::has($sName) )
		{
			// 尝试转换 xxx.ooo 为 xxx_ooo
			if( strpos($sName,'.')===false or !parent::has(str_replace('.','_',$sName)) )
			{
				return false ;
			}
		}
		return true ;
	}
	
	/**
	 * @return IFilterMangeger
	 */
	public function filters()
	{
		return $this->aFilters ;
	}
	
	public function setFilters(IFilterMangeger $aFilters)
	{
		$this->aFilters = $aFilters ;
	}
	
	static public function isUserRequest(IDataSrc $aRequest)
	{
		return $aRequest->bool(self::DATANAME_USERCALL) ;
	}
	
	const DATANAME_USERCALL = '.data-name-is-user-call' ;
	
	/**
	 * Enter description here ...
	 * 
	 * @var org\jecat\framework\io\PrintSteam
	 */
	private $aPrinter ;
}



