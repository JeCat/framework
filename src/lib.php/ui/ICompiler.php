<?php

namespace jc\ui ;

use jc\io\IOutputStream;

interface ICompiler
{
	/**
	 * @return ICompiled
	 */
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager) ;
}

?>