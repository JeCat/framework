<?php

namespace org\jecat\framework\ui ;

use org\jecat\framework\ui\TargetCodeOutputStream;

interface ICompiler
{
	/**
	 * @return ICompiled
	 */
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager) ;
	
	public function compileStrategySignture() ;
}

?>