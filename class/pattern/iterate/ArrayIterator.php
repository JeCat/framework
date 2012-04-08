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
namespace org\jecat\framework\pattern\iterate ;

use org\jecat\framework\lang\Object;

class ArrayIterator extends Object implements INonlinearIterator
{
	public function __construct(array $array=array())
	{
		$this->arrKeys = array_keys($array) ;
		$this->arrElements = array_values($array) ;
		
		$this->nEndPosition = count($this->arrKeys)-1 ;
	}
	
	public function position()
	{
		return $this->nPosition ;
	}
	
	public function current ()
	{
		if( isset($this->arrElements[$this->nPosition]) )
		{
			return $this->arrElements[$this->nPosition] ;
		}
		
		else 
		{
			return $ret=null ;
		}
	}

	public function next ()
	{
		if( $this->nPosition<=$this->nEndPosition )
		{
			$this->nPosition ++ ;
		}
	}

	public function key ()
	{
		return isset($this->arrKeys[$this->nPosition])?
				$this->arrKeys[$this->nPosition]: null ;
	}

	public function valid ()
	{
		return $this->nPosition>=0 and $this->nPosition<=$this->nEndPosition ;
	}

	public function rewind ()
	{
		$this->nPosition = 0 ;
	}
	
	public function prev()
	{
		if( $this->nPosition>=0 )
		{
			$this->nPosition -- ;
		}
	}
	
	public function last()
	{
		$this->nPosition = $this->nEndPosition ;
	}

	public function seek ($nPosition)
	{
		if($nPosition<0)
		{
			$nPosition = -1 ;
		}
		
		if( $nPosition>$this->nEndPosition )
		{
			$nPosition = $this->nEndPosition + 1 ;
		}
		
		$this->nPosition = $nPosition ;
	}
	
	public function search ($element)
	{
		return array_search($element,$this->arrElements,true) ;
	}
	
	public function searchKey ($key)
	{
		if( is_object($key) )
		{
			$key = strval($key) ;
		}
		return array_search($key,$this->arrKeys,true) ;
	}
	
	private $nPosition = 0 ;
	
	private $nEndPosition = 0 ;
	
	private $arrKeys ;
	
	private $arrElements ;
}

