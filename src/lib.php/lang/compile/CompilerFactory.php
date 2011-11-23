<?php
namespace jc\lang\compile ;

use jc\lang\aop\AOP;
use jc\lang\Object;

class CompilerFactory extends Object
{
	/**
	 * return Compiler
	 */
	public function create()
	{
		$aCompiler = new Compiler() ;

		//--------------------
		// interpreter
		$aCompiler->registerInterpreter("jc\\lang\\compile\\interpreters\\ClosureObjectParser") ;
		$aCompiler->registerInterpreter("jc\\lang\\compile\\interpreters\\oop\\SyntaxScanner") ;

		// generator
		$aCompiler->registerGenerator("jc\\lang\\compile\\object\\FunctionDefine","jc\\lang\\compile\\generators\\CompiledAlert") ;
		$aCompiler->registerGenerator("jc\\lang\\compile\\object\\FunctionDefine","jc\\lang\\aop\\compiler\\FunctionDefineGenerator") ;
		$aCompiler->registerGenerator("jc\\lang\\compile\\object\\CallFunction","jc\\lang\\aop\\compiler\\CallFunctionGenerator") ;
		
		//--------------------
		// 添加编译策略概要，用于生成编译器的”策略签名“
		$aCompiler->addStrategySummary("jc\\lang\\compile\\interpreters\\ClosureObjectParser") ;
		$aCompiler->addStrategySummary("jc\\lang\\compile\\interpreters\\oop\\SyntaxScanner") ;
		$aCompiler->addStrategySummary("jc\\lang\\compile\\interpreters\\oop\\PHPCodeParser") ;
		$aCompiler->addStrategySummary("jc\\lang\\compile\\interpreters\\oop\\NamespaceDeclareParser") ;
		$aCompiler->addStrategySummary("jc\\lang\\compile\\interpreters\\oop\\ClassDefineParser") ;
		$aCompiler->addStrategySummary("jc\\lang\\compile\\interpreters\\oop\\FunctionDefineParser") ;
		$aCompiler->addStrategySummary("jc\\lang\\compile\\interpreters\\oop\\CallFunctionParser") ;
		$aCompiler->addStrategySummary("jc\\lang\\compile\\object\\FunctionDefine<jc\\lang\\compile\\generators\\CompiledAlert") ;
		
		$aCompiler->addStrategySummary(AOP::singleton()) ;
		
		return $aCompiler ;
	}
}

?>