<?php
namespace jc\ui\xhtml\parsers ;

use jc\lang\Assert;
use jc\ui\xhtml\IObject;
use jc\ui\xhtml\Text;
use jc\ui\xhtml\ObjectBase;
use jc\ui\xhtml\Tag;
use jc\lang\Object;
use jc\util\String;

class ParserStateText extends ParserState
{
	public function __construct()
	{
		parent::__construct() ;
		
		$this->arrChangeToStates[] = ParserStateTag::singleton() ;
		$this->arrChangeToStates[] = ParserStateMark::singleton() ;
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
		Assert::type("jc\\ui\\xhtml\\Text", $aObject, 'aObject') ;
		
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

?>