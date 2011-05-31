<?php
namespace jc\ui\xhtml\parsers ;

use jc\ui\xhtml\ObjectBase;
use jc\ui\xhtml\Mark;
use jc\ui\xhtml\IObject;
use jc\util\String;

class ParserStateMark extends ParserState
{
	public function active(IObject $aParent,String $aSource,$nPosition)
	{
		$aMark = new Mark($nPosition, 0, ObjectBase::getLine($aSource,0,$nPosition), '') ;
		$aParent->add($aMark) ;
		
		return $aMark ;
	}
	
	public function examineEnd(String $aSource, &$nPosition,IObject $aObject) 
	{
		return $aSource->byte($nPosition)=='}' ;
	}
	
	public function examineStart(String $aSource, &$nPosition,IObject $aObject)
	{
		return $aSource->byte($nPosition)=='{' and in_array($aSource->byte($nPosition+1),array('?','=','*')) ;
	}
}

?>