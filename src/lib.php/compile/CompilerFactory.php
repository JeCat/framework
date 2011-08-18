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
		
		$aCompiler->registerInterpreter("jc\\compile\\interpreters\\ClosureObjectParser") ;
		$aCompiler->registerInterpreter("jc\\compile\\interpreters\\oop\\SyntaxScanner") ;
		
		// 添加编译策略概要，用于生成编译器的”策略签名“
		$aCompiler->addStrategySummary("jc\\compile\\interpreters\\ClosureObjectParser") ;
		$aCompiler->addStrategySummary("jc\\compile\\interpreters\\oop\\SyntaxScanner") ;
		
		return $aCompiler ;
	}
}

?>