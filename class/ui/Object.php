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

namespace org\jecat\framework\ui ;

use org\jecat\framework\util\HashTable;
use org\jecat\framework\mvc\controller\Response;
use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\pattern\composite\Composite;

class Object extends Composite implements IObject
{
	public function __construct()
	{
		$this->addAcceptClasses('*') ;
	}

	// implement for IObject //////////////////	
	public function depth()
	{
		$aParent = $this->parent() ;
		return $aParent? $aParent->depth()+1: 0 ;
	}
	
	public function childAfter(self $aFind)
	{
		$aIter = $this->iterator() ;
		foreach($aIter as $aChild)
		{
			if($aFind==$aChild)
			{
				$aIter->next() ;
				return $aIter->current() ;
			}
		}
		
		return null ;
	}
	
	
	public function summary()
	{
		return "[Class:".get_class($this)."]" ;
	}

	public function printStruct(IOutputStream $aDevice=null,$nDepth=0)
	{
		if(!$aDevice)
		{
			$aDevice = Response::singleton()->printer() ;
		}
		
		$aDevice->write( str_repeat("\t",$nDepth) . $this->summary() . "\r\n" ) ;
		foreach ($this->iterator() as $aChild)
		{
			$aChild->printStruct($aDevice,$nDepth+1) ;
		}
	}
	
	/**
	 * @return org\jecat\framework\util\IHashTable
	 */
	public function properties($bAutoCreate=true)
	{
		if(!$this->aProperties)
		{
			$this->aProperties = new HashTable() ;
		}
		return $this->aProperties ;
	}
	
	/**
	 * @return ObjectContainer
	 */
	public function objectContainer()
	{
		$aParent = $this ;
		while($aParent = $aParent->parent())
		{
			if( $aParent instanceof ObjectContainer )
			{
				return $aParent ;
			}
		}
	}
	
	private $aProperties ;
}

