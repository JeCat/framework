<?php
namespace jc\compile ;

use jc\lang\Object;

class CompilerFactory extends Object
{
	/**
	 * return Compiler
	 */
	public function create()
	{
		$aCompiler = new Compiler() ;
		
		return $aCompiler ;
	}
}

?>