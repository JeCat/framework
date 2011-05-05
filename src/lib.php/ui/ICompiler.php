<?php

namespace jc\ui ;

interface ICompiler
{
	/**
	 * return jc\fs\IFile
	 */
	public function createCompiledFile($sCompiledPath) ;
	
	/**
	 * @return IObject
	 */
	public function loadCompiled($sCompiledPath) ;
	
	/**
	 * @return ICompiled
	 */
	public function compile(IObject $aObject,$sCompiledPath) ;
}

?>