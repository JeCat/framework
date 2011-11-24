<?php
namespace org\jecat\framework\ui\xhtml\parsers ;

use org\jecat\framework\ui\xhtml\ObjectBase;

use org\jecat\framework\ui\xhtml\IObject;
use org\jecat\framework\util\String;

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