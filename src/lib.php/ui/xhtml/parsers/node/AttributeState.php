<?php
namespace jc\ui\xhtml\parsers\node ;

use jc\lang\Type;
use jc\ui\xhtml\ObjectBase;
use jc\ui\IObject;
use jc\ui\xhtml\Tag;
use jc\lang\Object;
use jc\util\String;

class AttributeState extends Object implements IParserState
{
	public function wakeup(Parser $aParser,String $aSource,$nProcIndex)
	{
		$aParser->setCurrentObject(
			new AttributeValue( $aSource->substr($nProcIndex,1), $nProcIndex, ObjectBase::getLine($aSource, $nProcIndex) )
		) ;
	}
	
	public function process(String $aSource,$nProcIndex,Parser $aParser,IObject $aObjectContainer)
	{
		$sByte = $aSource->substr($nProcIndex,1) ;
		
		$aAttributeValue = $aParser->currentObject() ;
		Type::check('jc\\ui\\xhtml\\parsers\\node\\AttributeValue', $aAttributeValue) ;
		
		// 转移符
		if( $sByte=='\\' )
		{
			// 跳过下一个字符
			$nProcIndex ++ ;
		}
		
		// 属性结束边界
		else if( $sByte==$aAttributeValue->quoteType() )
		{
			// 切换状态
			$aParser->switchState(
				TagState::singleton()
				, $aSource, $nProcIndex
			) ;
		}
		
		return $nProcIndex + 1 ;
	}
	
	public function sleep(String $aSource,$nEndPosition,ObjectBase $aObject,IObject $aObjectContainer)
	{
		Type::check('jc\\ui\\xhtml\\parsers\\node\\AttributeValue', $aObject) ;
		
		$sAttrTextStartIdx = $aObject->position()+1 ;
		$sAttrTextLen = ($nEndPosition-1) - $sAttrTextStartIdx + 1 ;
		$sAttrText = $aSource->substr(
			$sAttrTextStartIdx
			, $sAttrTextLen
		) ;
		
		$aSource->substrreplace( str_repeat('.', $sAttrTextLen), $sAttrTextStartIdx,$sAttrTextLen ) ;
	}
}

?>