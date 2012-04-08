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
namespace org\jecat\framework\ui\xhtml\weave ;

use org\jecat\framework\ui\xhtml\Node;
use org\jecat\framework\ui\xhtml\Macro;
use org\jecat\framework\ui\xhtml\Text;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\lang\Exception;

class PatchSlotPathSegment 
{
	private function __construct()
	{}
	
	/**
	 * @return ObjectPathSegment
	 */
	static public function parseSegment($sSegment)
	{
		$aObjectPathSegment = new self() ;
		
		if( strstr($sSegment,'@')===false )
		{
			if(is_numeric($sSegment))
			{
				$aObjectPathSegment->nPos = intval($sSegment) ;
				$aObjectPathSegment->sObjectType = '*' ;
			}
		}
		else
		{
			list($aObjectPathSegment->sObjectType,$sPos) = explode('@',$sSegment,2) ;
			
			if( !is_numeric($sPos) )
			{
				throw new Exception("遇到无效的路径片段:%s，其中%s部分必须是一个数字",array($sSegment,$sPos)) ;
			}
			$aObjectPathSegment->nPos = intval($sPos) ;
		}
		
		$aObjectPathSegment->sObjectType = strtolower($aObjectPathSegment->sObjectType) ;
		
		return $aObjectPathSegment ;
	}
	
	public function __toString()
	{
		return "{$this->sObjectType}@{$this->nPos}" ;
	}
	
	/**
	 * @return org\jecat\framework\ui\xhtml\ObjectBase
	 */
	public function localObject(IObject $aParentObject)
	{
		$nPos = 0 ;
		
		foreach($aParentObject->iterator() as $aBrother)
		{
			if( $this->matchType($aBrother) and $nPos++==$this->nPos )
			{
				return $aBrother ;
			}
		}
		
		return null ;
	}
	
	public function matchType(IObject $aObject)
	{
		switch ($this->sObjectType)
		{
			case '*' :
				return true ;
			case '<text>' :
				return ($aObject instanceof Text) ;
			case '<macro>' :
				return ($aObject instanceof Macro) ;
			default:
				return ($aObject instanceof Node) and $aObject->tagName()==$this->sObjectType ;
		}
	}
	
	static public function xpathType(IObject $aObject)
	{
		if( $aObject instanceof Text )
		{
			return '<text>' ;
		}
		else if( $aObject instanceof Macro )
		{
			return '<macro>' ;
		}
		else if( $aObject instanceof Node )
		{
			return $aObject->tagName() ;
		}
		else 
		{
			return '<unknow>' ;
		}
	}
	
	private $sObjectType ;
	
	private $nPos ;
}

