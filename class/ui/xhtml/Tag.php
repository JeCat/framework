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


class Tag extends ObjectBase
{
	const TYPE_HEAD = 1 ;
	const TYPE_TAIL = 4 ;
	const TYPE_SINGLE = 3 ;
	
	public function __construct($sName,Attributes $aAttrs=null,$nType,$nPosition,$nEndPosition,$nLine,$sSource)
	{
		$this->sName = $sName ;
		$this->nType = $nType ;
		$this->aAttrs = $aAttrs? $aAttrs: new Attributes() ;
		
		parent::__construct($nPosition, $nEndPosition, $nLine, $sSource) ;
	}

	public function name()
	{
		return $this->sName ;
	}
	public function setName($sName)
	{
		$this->sName = $sName ;
	}
	
	/**
	 * @return Attributes
	 */
	public function attributes()
	{
		return $this->aAttrs ;
	}
	/**
	 * @return Attributes
	 */
	public function setAttributes($aAttrs)
	{
		$this->aAttrs = $aAttrs ;
	}
	
	public function tagType()
	{
		return $this->nType ;
	}
	public function setTagType($nType)
	{
		$this->nType = $nType ;
	}
	
	public function isHead()
	{
		return ($this->nType&self::TYPE_HEAD)==self::TYPE_HEAD ;
	}
	public function isTail()
	{
		return ($this->nType&self::TYPE_TAIL)==self::TYPE_TAIL ;
	}
	public function isSingle()
	{
		return ($this->nType&self::TYPE_SINGLE)==self::TYPE_SINGLE ;
	}
	
	public function add($aChild,$sName=null,$bTakeover=true)
	{
		if( $aChild instanceof Macro )
		{
			$aAttrVal = new AttributeValue(null, '', $aChild->position(), $aChild->line()) ;
			$aAttrVal->setEndPosition($aChild->endPosition()) ;
			$aAttrVal->add($aChild) ;
			
			$this->attributes()->add($aAttrVal) ;
			
			$aAttrVal->setParent($this) ;
			$aChild->setParent($this) ;
		}
	}
	
	private $sName ;
	private $aAttrs ;
	private $nType ;
}
