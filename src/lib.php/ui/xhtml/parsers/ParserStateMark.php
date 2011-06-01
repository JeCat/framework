<?php
namespace jc\ui\xhtml\parsers ;

use jc\lang\Exception;
use jc\lang\Assert;
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
		$sByte = $aSource->byte($nPosition) ;
		
		if( in_array($sByte,array('{')) )
		{
			throw new Exception('分析UI模板Mark对象时遇到无效的字符：%s(位置：%d行)',array(
						$sByte, $aObject->line()
			)) ;
		}
		
		if( in_array($sByte,array("\r","\n")) )
		{
			throw new Exception('分析UI模板Mark对象时遇到换行，Mark对象只能在单行内书写(位置：%d行)',$aObject->line()) ;
		}
		
		return $sByte=='}' ;
	}
	
	public function complete(IObject $aObject,String $aSource,$nPosition)
	{
		Assert::type("jc\\ui\\xhtml\\Mark", $aObject, 'aObject') ;
		
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