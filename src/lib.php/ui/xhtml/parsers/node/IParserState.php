<?php
namespace jc\ui\xhtml\parsers\node ;

use jc\ui\xhtml\ObjectBase;
use jc\util\String;
use jc\ui\IObject;

interface IParserState
{	
	public function process(String $aSource,$nProcIndex,Parser $aParser,IObject $aObjectContainer) ;
	
	public function wakeup(Parser $aParser,String $aSource,$nProcIndex) ;
	
	public function sleep(String $aSource,$nProcIndex,ObjectBase $aObject,IObject $aObjectContainer) ;
}

?>