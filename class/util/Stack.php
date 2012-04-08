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
namespace org\jecat\framework\util ;

use org\jecat\framework\lang\Object;

class Stack extends Object
{
	public function __construct(array $arrDataStack=array())
	{
		parent::__construct() ;
		
		$this->arrDataStack = $arrDataStack ;
	}

	public function isEmpty()
	{
		return count($this->arrDataStack)==0 ;
	}
	
	public function put($element) 
	{
		$this->arrDataStack[] = $element ;
	}
	
	public function get() 
	{
		return end($this->arrDataStack) ;
	}

	public function out()
	{
		if( count($this->arrDataStack) )
		{
			return array_pop($this->arrDataStack) ;
		}
		return null ;
	}
	
	public function putRef(&$element) 
	{
		$this->arrDataStack[] =& $element ;
	}
	
	public function & outRef()
	{
		if(empty($this->arrDataStack))
		{
			$var = null ;
		}
		else
		{
			end($this->arrDataStack) ;
			$nIdx = key($this->arrDataStack) ;
			
			$var =& $this->arrDataStack[$nIdx] ;
			unset($this->arrDataStack[$nIdx]) ;
		}
		
		return $var ;
	}
	
	public function & getRef()
	{
		if(empty($this->arrDataStack))
		{
			$var = null ;
			return $var ;
		}
		else
		{
			end($this->arrDataStack) ;
			return $this->arrDataStack[key($this->arrDataStack)] ;
		}
	}
	
	public function length()
	{
		return count($this->arrDataStack) ;
	}
	
	/**
	 * @return org\jecat\framework\pattern\iterate\INonlinearIterator
	 */
	public function iterator()
	{
		return new \org\jecat\framework\pattern\iterate\ArrayIterator($this->arrDataStack) ;
	}

	private $arrDataStack ;
}

