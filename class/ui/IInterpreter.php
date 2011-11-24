<?php
namespace org\jecat\framework\ui ;

use org\jecat\framework\fs\IFile;
use org\jecat\framework\util\String;

interface IInterpreter
{
	public function parse(String $aSource,ObjectContainer $aObjectContainer) ; 
}

?>