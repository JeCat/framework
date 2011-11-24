<?php
namespace jc\ui\xhtml\parsers ;

use jc\ui\xhtml\ObjectBase;

use jc\ui\xhtml\IObject;
use jc\util\String;

class ParserStateDefault extends ParserState
{
	public function __construct()
	{
		parent::__construct() ;
		self::setSingleton($this) ;
		
		$this->arrChangeToStates[] = ParserStateTag::singleton() ;
		$this->arrChangeToStates[] = ParserStateMacro::singleton() ;
		$this->arrChangeToStates[] = ParserStateText::singleton() ;
	}
	
	public function active(IObject $aParent,String $aSource,$nPosition)
	{
		return null ;
	}
	
	public function examineEnd(String $aSource, &$nPosition,IObject $aObject)
	{
		return $aSource->length()-1<=$nPosition ;
	}
	public function examineStart(String $aSource, &$nPosition,IObject $aObject)
	{
		return $aSource->length()>$nPosition ;
	}
}

?>