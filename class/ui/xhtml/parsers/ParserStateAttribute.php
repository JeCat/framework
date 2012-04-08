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
namespace org\jecat\framework\ui\xhtml\parsers ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\ui\xhtml\AttributeValue;
use org\jecat\framework\ui\xhtml\IObject;
use org\jecat\framework\util\String;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\xhtml\ObjectBase;

class ParserStateAttribute extends ParserState
{
	public function __construct()
	{
		parent::__construct() ;
		self::setSingleton($this) ;
		
		$this->arrChangeToStates[__NAMESPACE__.'\\ParserStateMacro'] = ParserStateMacro::singleton() ;
	}
	
	public function active(IObject $aParent,String $aSource,$nPosition)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Tag", $aParent, 'aParent') ;
		
		$sByte = $aSource->byte($nPosition) ;
				
		if( in_array($sByte,array('"',"'")) )
		{
			$nStartPos = $nPosition+1 ;
			$sBorder = $sByte ;
		}
		else 
		{
			$nStartPos = $nPosition ;
			$sBorder = null ;
		}

		$aAttriVal = new AttributeValue( null, $sBorder, $nStartPos, ObjectBase::getLine($aSource, $nStartPos) ) ;
		$aParent->attributes()->add($aAttriVal) ;
		$aAttriVal->setParent($aParent) ;
	
		if( $sByte=='=' )
		{
			$aAttriVal->setEndPosition($nPosition) ;
			$aAttriVal->setSource($sByte) ;
			
			return $aParent ;
		}
		else 
		{			
			// 只有一个字节的属性值
			$nNextPos = $nPosition+1 ;
			if( ParserStateTag::singleton()->examineEnd($aSource,$nNextPos,$aAttriVal) )
			{
				return $this->complete($aAttriVal, $aSource, $nPosition) ;
			}
			
			return $aAttriVal ;
		}
	}

	public function complete(IObject $aObject,String $aSource,$nPosition)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\AttributeValue", $aObject, 'aObject') ;

		$sByte = $aSource->byte($nPosition) ;
		
		if( $aObject->quoteType() and $aObject->quoteType()!=$sByte )
		{
			throw new Exception("属性前后边界类型不符，位置：%d行",$aObject->line()) ;
		}
		
		$sAttrTextPos = $aObject->position() ;
		$sAttrTextEndPos = $aObject->quoteType()? $nPosition-1: $nPosition ;
		
		$sAttrTextLen = $sAttrTextEndPos - $sAttrTextPos + 1 ;
		$sAttrText = $aSource->substr( $sAttrTextPos, $sAttrTextLen ) ;
		
		$aObject->setPosition($sAttrTextPos) ;
		$aObject->setEndPosition($sAttrTextEndPos) ;
		$aObject->setSource($sAttrText) ;
		
		// 子对象 分离
		$aObject->separateChildren() ;
		
		return $aObject->parent() ;
	}

	public function examineEnd(String $aSource, &$nPosition,IObject $aObject)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\AttributeValue", $aObject, 'aObject') ;
		
		$sByte = $aSource->byte($nPosition) ;
		$sQuote = $aObject->quoteType() ;
		
		// 有引号的
		if($sQuote)
		{
			if($aObject->quoteType()==$sByte)
			{
				return true ;
			}
			else 
			{
				if($sByte=='\\')
				{
					$nPosition ++ ;
				}

				return false ;
			}
		}
		
		// 无引号的
		else 
		{
			$sNextByte = $aSource->byte($nPosition+1) ;
			if( in_array($sNextByte,array('=','>')) )
			{
				return true ;
			}
			
			// 空白字符
			if( preg_match('/\\s/',$sNextByte) )
			{
				return true ;
			}

			return false ;
		}
	}
	public function examineStart(String $aSource, &$nPosition,IObject $aCurrentObject)
	{		
		return preg_match("|^[^\\s/]$|", $aSource->byte($nPosition)) ;
	}
	
}

