<?php
namespace jc\lang\compile ;

use jc\lang\Object;

class CompilerFactory extends Object
{
	/**
	 * return Compiler
	 */
	public function create()
	{
		$aCompiler = new Compiler() ;
		
		$aCompiler->registerInterpreter("jc\\lang\\compile\\interpreters\\ClosureObjectParser") ;
		$aCompiler->registerInterpreter("jc\\lang\\compile\\interpreters\\oop\\SyntaxScanner") ;
		
		$aCompiler->registerGenerator("jc\lang\compile\object\FunctionDefine","jc\\lang\\compile\\generators\\CompiledAlert") ;
		
		// 添加编译策略概要，用于生成编译器的”策略签名“
		$aCompiler->addStrategySummary("jc\\lang\\compile\\interpreters\\ClosureObjectParser") ;
		$aCompiler->addStrategySummary("jc\\lang\\compile\\interpreters\\oop\\SyntaxScanner") ;
		$aCompiler->addStrategySummary("jc\\lang\\compile\\interpreters\\oop\\PHPCodeParser") ;
		$aCompiler->addStrategySummary("jc\\lang\\compile\\interpreters\\oop\\NamespaceParser") ;
		$aCompiler->addStrategySummary("jc\\lang\\compile\\interpreters\\oop\\ClassDefineParser") ;
		$aCompiler->addStrategySummary("jc\\lang\\compile\\interpreters\\oop\\FunctionDefineParser") ;
		
		$aCompiler->addStrategySummary("jc\\lang\\compile\\object\\FunctionDefine<jc\\lang\\compile\\generators\\CompiledAlert") ;
		
		return $aCompiler ;
	}
}

?>