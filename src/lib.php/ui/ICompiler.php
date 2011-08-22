<?php

namespace jc\ui ;

use jc\ui\TargetCodeOutputStream;

interface ICompiler
{
	/**
	 * @return ICompiled
	 */
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager) ;
}

?>