<?php
namespace jc\ui ;

use jc\util\String;

interface IInterpreter
{
	public function parse(String $aSource,IObject $aObjectContainer,$sSourcePath) ; 
}

?>