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

use org\jecat\framework\lang\Object;

abstract class Key extends Object implements IKey
{
	public function item($sName='*',$sDefault=null)
	{
		if( !$this->hasItem($sName) )
		{
			if($sDefault===null)
			{
				return null ;
			}
			else 
			{
				$this->arrItems[$sName] = $sDefault ;
				$this->bDataChanged = true ;
			}
		}
		
		return $this->arrItems[$sName] ; 
	}
	
	public function setItem($sName,$value)
	{
		if( !array_key_exists($sName,$this->arrItems) or $this->arrItems[$sName]!==$value)
		{
			$this->bDataChanged = true ;
		}
		$this->arrItems[$sName] = $value ;
	}
	
	public function hasItem($sName)
	{
		return array_key_exists($sName,$this->arrItems) ;
	}
	
	public function deleteItem($sName)
	{
		unset($this->arrItems[$sName]) ;
		$this->bDataChanged = true ;
	}
	
	/**
	 * @return \Iterator 
	 */
	public function itemIterator()
	{
		return new \ArrayIterator( array_keys($this->arrItems) ) ;
	}
	
	public function __destruct()
	{
		if( $this->bDataChanged )
		{
			$this->save() ;
		}
	}
	
	// implements ArrayAccess	
	/**
	 * @param offset
	 */
	public function offsetExists ($offset)
	{
		return isset($this->arrItems[$offset]) ;
	}
	
	/**
	 * @param offset
	 */
	public function offsetGet ($offset)
	{
		return $this->item($offset) ;
	}
	
	/**
	 * @param offset
	 * @param value
	 */
	public function offsetSet ($offset, $value)
	{
		return $this->setItem($offset, $value) ;
	}
	
	/**
	 * @param offset
	 */
	public function offsetUnset ($offset)
	{
		return $this->deleteItem($offset) ;
	}
	
	protected $arrItems = array() ;
	
	protected $bDataChanged = false ;

}
