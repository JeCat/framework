<?php
namespace jc\ui ;

use jc\fs\IFile;
use jc\util\String;

interface IInterpreter
{
	public function parse(String $aSource,IObject $aObjectContainer,IFile $aSourceFile) ; 
}

?>