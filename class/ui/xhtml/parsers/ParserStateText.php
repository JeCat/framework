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

use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\xhtml\IObject;
use org\jecat\framework\ui\xhtml\Text;
use org\jecat\framework\ui\xhtml\ObjectBase;
use org\jecat\framework\util\String;

class ParserStateText extends ParserState
{
	public function __construct()
	{
		parent::__construct() ;
		self::setSingleton($this) ;
		
		$this->arrChangeToStates[__NAMESPACE__.'\\ParserStateTag'] = ParserStateTag::singleton() ;
		$this->arrChangeToStates[__NAMESPACE__.'\\ParserStateMacro'] = ParserStateMacro::singleton() ;
	}
	
	public function active(IObject $aParent,String $aSource,$nPosition)
	{
		$aText = new Text($nPosition, 0, ObjectBase::getLine($aSource,$nPosition), '') ;
		$aParent->add($aText) ;
		
		return $aText ;
	}
	
	public function sleep(IObject $aObject,String $aSource,$nPosition)
	{
		return $this->complete($aObject,$aSource,$nPosition) ;
	}

	public function complete(IObject $aObject,String $aSource,$nPosition)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Text", $aObject, 'aObject') ;
		
		$sTextPos = $aObject->position() ;
		$sTextLen = $nPosition - $sTextPos + 1 ;
		$sText = $aSource->substr( $sTextPos, $sTextLen ) ;
		
		$aObject->setEndPosition($nPosition) ;
		$aObject->setSource($sText) ;
		
		return $aObject->parent() ;
	}
		
	public function examineEnd(String $aSource, &$nPosition,IObject $aObject)
	{
		return $aSource->length()-1==$nPosition ;
	}
	public function examineStart(String $aSource, &$nPosition,IObject $aObject)
	{
		return $aSource->length()>$nPosition ;
	}
	
}

