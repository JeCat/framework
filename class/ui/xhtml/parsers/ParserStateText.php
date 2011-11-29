<?php
namespace org\jecat\framework\ui\xhtml\parsers ;

use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\xhtml\IObject;
use org\jecat\framework\ui\xhtml\Text;
use org\jecat\framework\ui\xhtml\ObjectBase;
use org\jecat\framework\ui\xhtml\Tag;
use org\jecat\framework\lang\Object;
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

?>