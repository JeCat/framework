<?php
namespace org\jecat\framework\lang\compile ;

use org\jecat\framework\lang\aop\AOP;
use org\jecat\framework\lang\Object;

class CompilerFactory extends Object
{
	/**
	 * return Compiler
	 */
	public function create(Compiler $aCompiler=null)
	{
		if(!$aCompiler)
		{
			$aCompiler = new Compiler() ;
		}

		//--------------------
		// interpreter
		$aCompiler->registerInterpreter("org\\jecat\\framework\\lang\\compile\\interpreters\\ClosureObjectParser") ;
		$aCompiler->registerInterpreter("org\\jecat\\framework\\lang\\compile\\interpreters\\oop\\SyntaxScanner") ;

		// generator
		$aCompiler->registerGenerator("org\\jecat\\framework\\lang\\compile\\object\\FunctionDefine","org\\jecat\\framework\\lang\\compile\\generators\\CompiledAlert") ;
		$aCompiler->registerGenerator("org\\jecat\\framework\\lang\\compile\\object\\ClassDefine","org\\jecat\\framework\\lang\\aop\\compiler\\FunctionDefineGenerator") ;
		// $aCompiler->registerGenerator("org\\jecat\\framework\\lang\\compile\\object\\CallFunction","org\\jecat\\framework\\lang\\aop\\compiler\\CallFunctionGenerator") ;
		
		//--------------------
		// 添加编译策略概要，用于生成编译器的”策略签名“
		$aCompiler->addStrategySummary("org\\jecat\\framework\\lang\\compile\\interpreters\\ClosureObjectParser") ;
		$aCompiler->addStrategySummary("org\\jecat\\framework\\lang\\compile\\interpreters\\oop\\SyntaxScanner") ;
		$aCompiler->addStrategySummary("org\\jecat\\framework\\lang\\compile\\interpreters\\oop\\PHPCodeParser") ;
		$aCompiler->addStrategySummary("org\\jecat\\framework\\lang\\compile\\interpreters\\oop\\NamespaceDeclareParser") ;
		$aCompiler->addStrategySummary("org\\jecat\\framework\\lang\\compile\\interpreters\\oop\\UseDeclareParser") ;
		$aCompiler->addStrategySummary("org\\jecat\\framework\\lang\\compile\\interpreters\\oop\\ClassDefineParser") ;
		$aCompiler->addStrategySummary("org\\jecat\\framework\\lang\\compile\\interpreters\\oop\\FunctionDefineParser") ;
		$aCompiler->addStrategySummary("org\\jecat\\framework\\lang\\compile\\interpreters\\oop\\CallFunctionParser") ;
		$aCompiler->addStrategySummary("org\\jecat\\framework\\lang\\compile\\object\\FunctionDefine<org\\jecat\\framework\\lang\\compile\\generators\\CompiledAlert") ;
		
		$aCompiler->addStrategySummary(AOP::singleton()) ;
		
		return $aCompiler ;
	}
}

?>