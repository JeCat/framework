<?php
namespace jc\ui ;

interface IInterpreter
{
	/**
	 * return IObject
	 */
	public function parse($sSourcePath) ; 
}

?>