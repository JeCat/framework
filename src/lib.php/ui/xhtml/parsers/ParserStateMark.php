<?php
namespace jc\ui\xhtml\parsers ;

use jc\lang\Type;

use jc\ui\xhtml\ObjectBase;
use jc\ui\xhtml\Mark;
use jc\ui\xhtml\IObject;
use jc\util\String;

class ParserStateMark extends ParserState
{
	public function active(IObject $aParent,String $aSource,$nPosition)
	{
		$aMark = new Mark($aSource->byte($nPosition+1),$nPosition+2, 0, ObjectBase::getLine($aSource,$nPosition), '') ;
		$aParent->add($aMark) ;
		
		return $aMark ;
	}
	
	public function examineEnd(String $aSource, &$nPosition,IObject $aObject) 
	{
		return $aSource->byte($nPosition)=='}' ;
	}
	
	public function complete(IObject $aObject,String $aSource,$nPosition)
	{
		Type::assert("jc\\ui\\xhtml\\Mark", $aObject, 'aObject') ;
		
		$sTextPos = $aObject->position() ;
		$sTextLen = ($nPosition-1) - $sTextPos + 1 ;
		$sText = $aSource->substr( $sTextPos, $sTextLen ) ;
		
		$aObject->setEndPosition($nPosition-1) ;
		$aObject->setSource($sText) ;
		
		return $aObject->parent() ;
	}
	
	public function examineStart(String $aSource, &$nPosition,IObject $aObject)
	{
		return $aSource->byte($nPosition)=='{' and in_array($aSource->byte($nPosition+1),array('?','=','*')) ;
	}
}

?>