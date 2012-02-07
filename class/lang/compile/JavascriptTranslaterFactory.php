<?php
namespace org\jecat\framework\lang\compile ;

use org\jecat\framework\lang\compile\interpreters\oop\HereDocParser;

class JavascriptTranslaterFactory extends CompilerFactory
{
	/**
	 * return Compiler
	 */
	public function create()
	{
		$aCompiler = parent::create() ;
		
		$aCompiler->registerGenerator("org\\jecat\\framework\\lang\\compile\\object\\Token","org\\jecat\\framework\\lang\\compile\\generators\\translater\\JavascriptTranslater") ;
		$aCompiler->interpreter("org\\jecat\\framework\\lang\\compile\\interpreters\\oop\\SyntaxScanner")->addParser(new HereDocParser()) ;
		
		return $aCompiler ;
	}

}

?>