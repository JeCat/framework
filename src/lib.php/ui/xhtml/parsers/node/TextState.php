<?php
namespace jc\ui\xhtml\parsers\node ;

use jc\lang\Type;
use jc\ui\IObject;
use jc\ui\xhtml\Text;
use jc\ui\xhtml\ObjectBase;
use jc\ui\xhtml\Tag;
use jc\lang\Object;
use jc\util\String;

class TextState extends Object implements IParserState
{
	public function wakeup(Parser $aParser,String $aSource,$nProcIndex)
	{
		$aParser->setCurrentObject(
			new Text($nProcIndex, 0, ObjectBase::getLine($aSource, $nProcIndex), '')
		) ;
	}
	
	public function process(String $aSource,$nProcIndex,Parser $aParser,IObject $aObjectContainer)
	{
		$sByte = $aSource->substr($nProcIndex,1) ;
		
		// 发现节点开始边界
		if( $sByte=='<' and preg_match('/[\w:_\-\.]/',$aSource->substr($nProcIndex+1,1)) )
		{
			// 切换状态
			$aParser->switchState(
				TagState::singleton()
				, $aSource, $nProcIndex
			) ;
		}
		
		return ++$nProcIndex ;
	}
	
	public function sleep(String $aSource,$nEndPosition,ObjectBase $aObject,IObject $aObjectContainer)
	{
		Type::check('jc\\ui\\xhtml\\Text', $aObject) ;
		
		$aObject->setEndPosition($nEndPosition) ;
		$sText = $aSource->substr($aObject->position(),$nEndPosition-$aObject->position()+1) ;
		$aObject->setSource($sText) ;
		
		$aObjectContainer->add($aObject) ;
	}
}

?>