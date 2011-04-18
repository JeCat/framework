<?php

namespace jc\ui ;

interface ICompiler
{
	public function isCompiledValid($sSourcePath,$sCompiledPath) ;
	
	/**
	 * return jc\fs\IFile
	 */
	public function createCompiledFile($sCompiledPath) ;
	
	/**
	 * @return IObject
	 */
	public function loadCompiled($sCompiledPath) ;
	
	public function saveCompiled(IObject $aObject,$sCompiledPath) ;
	
	/**
	 * @return IObject
	 */
	public function compile($sSourcePath,$sCompiledPath) ;
}

?>