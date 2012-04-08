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

namespace org\jecat\framework\ui\xhtml ;

use org\jecat\framework\util\CombinedIterator;

class Node extends ObjectBase
{	
	static public function type()
	{
		return __CLASS__ ;
	}
	
	public function __construct(Tag $aHeadTag, Tag $aTailTag=null)
	{
		$this->setHeadTag($aHeadTag) ;
		$this->setTailTag($aTailTag) ;
		
		$this->setPosition(
			$this->aHeadTag->position()
		) ;
		
		$this->setLine(
			$this->aHeadTag->line()
		) ;
		
		parent::__construct($this->position(),$this->endPosition(),$this->line(),'') ;
	}

	public function position()
	{
		return $this->aHeadTag->position() ;
	}

	public function endPosition()
	{
		return $this->aTailTag?
				$this->aTailTag->endPosition() :
				$this->aHeadTag->endPosition() ;
	}

	public function line()
	{
		return $this->aHeadTag->line() ;	
	}

	public function tagName()
	{
		return $this->aHeadTag->name() ;
	}
	
	
	/**
	 * @return Tag
	 */
	public function headTag()
	{
		return $this->aHeadTag ;
	}
	public function setHeadTag(Tag $aTag)
	{
		if($this->aHeadTag)
		{
			$this->aHeadTag->setParent(null) ;
		}
		
		$this->aHeadTag = $aTag ;
		$this->aHeadTag->setParent($this) ;
	}
	/**
	 * @return Tag
	 */
	public function tailTag()
	{
		return $this->aTailTag ;
	}
	public function setTailTag(Tag $aTag=null)
	{
		if($this->aTailTag)
		{
			$this->aTailTag->setParent(null) ;
		}
		
		$this->aTailTag = $aTag ;
		
		if($this->aTailTag)
		{
			$this->aTailTag->setParent($this) ;
		}
	}
	
	/**
	 * @return Attributes
	 */
	public function attributes()
	{
		return $this->headTag()->attributes() ;
	}
	
	public function iterator($nType=null)
	{
		return new CombinedIterator(
			$this->aHeadTag->attributes()->valueIterator()		// 属性
			, parent::iterator($nType)					// children
		) ;
	}
	
	public function childElementsIterator()
	{
		return parent::iterator() ;
	}

	public function summary()
	{
		return parent::summary() . "; tag name: " . $this->tagName() ;
	}
	
	public function getChildNodeByTagName($sTagName)
	{
		foreach($this->childElementsIterator() as $aChild)
		{
			if( ($aChild instanceof Node) and ($aChild->tagName()==$sTagName) )
			{
				return $aChild ;
			}
		}
	}
	
	private $aHeadTag ;
	
	private $aTailTag ;	
	
}
