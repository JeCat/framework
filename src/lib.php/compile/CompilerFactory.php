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
		
		$aCompiler->registerGenerator("jc\compile\object\FunctionDefine","jc\\compile\\generators\\CompiledAlert") ;
		
		// 添加编译策略概要，用于生成编译器的”策略签名“
		$aCompiler->addStrategySummary("jc\\compile\\interpreters\\ClosureObjectParser") ;
		$aCompiler->addStrategySummary("jc\\compile\\interpreters\\oop\\SyntaxScanner") ;
		$aCompiler->addStrategySummary("jc\\compile\\interpreters\\oop\\PHPCodeParser") ;
		$aCompiler->addStrategySummary("jc\\compile\\interpreters\\oop\\NamespaceParser") ;
		$aCompiler->addStrategySummary("jc\\compile\\interpreters\\oop\\ClassDefineParser") ;
		$aCompiler->addStrategySummary("jc\\compile\\interpreters\\oop\\FunctionDefineParser") ;
		
		$aCompiler->addStrategySummary("jc\compile\object\FunctionDefine<jc\\compile\\generators\\CompiledAlert") ;
		
		return $aCompiler ;
	}
}

?>