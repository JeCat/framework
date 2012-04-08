<?php
namespace org\jecat\framework\ui\xhtml\parsers ;

use org\jecat\framework\ui\xhtml\ObjectBase;
use org\jecat\framework\ui\xhtml\Node;
use org\jecat\framework\ui\xhtml\Tag;
use org\jecat\framework\ui\xhtml\IObject;
use org\jecat\framework\util\String;

class ParserStateNode extends ParserState
{
	public function __construct()
	{
		parent::__construct() ;
		self::setSingleton($this) ;
		
		$this->arrChangeToStates[__NAMESPACE__.'\\ParserStateTag'] = ParserStateTag::singleton() ;
		$this->arrChangeToStates[__NAMESPACE__.'\\ParserStateMacro'] = ParserStateMacro::singleton() ;
		$this->arrChangeToStates[__NAMESPACE__.'\\ParserStateText'] = ParserStateText::singleton() ;
	}
	public function active(IObject $aParent,String $aSource,$nPosition)
	{
		return null ;
	}
	public function examineEnd(String $aSource, &$nPosition,IObject $aObject)
	{		
		return false ;
	}
	public function examineStart(String $aSource, &$nPosition,IObject $aObject)
	{
		return false ;
	} 

	public function complete(IObject $aObject,String $aSource,$nPosition)
	{
		$aHead = $aObject->headTag() ;
		$aTail = $aObject->tailTag()?:$aObject->headTag() ;
		
		$sSource = $aSource->substr($aHead->position(),$aTail->endPosition()-$aHead->position()+1) ;
		$aObject->setSource($sSource) ;
		
		return $aObject->parent() ;
	}
}

?>