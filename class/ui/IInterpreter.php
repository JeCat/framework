<?php
namespace org\jecat\framework\ui ;

use org\jecat\framework\fs\File;
use org\jecat\framework\util\String;

interface IInterpreter
{
	public function parse(String $aSource,ObjectContainer $aObjectContainer,UI $aUI) ; 
	
	public function compileStrategySignture() ;
}

?>