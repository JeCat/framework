<?php

namespace org\jecat\framework\ui ;

use org\jecat\framework\ui\TargetCodeOutputStream;

interface ICompiler
{
	/**
	 * @return ICompiled
	 */
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager) ;
}

?>