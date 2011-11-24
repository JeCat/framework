<?php
namespace jc\ui ;

interface IFactory
{
	/**
	 * return UI
	 */
	public function create() ;
	
	/**
	 * return ISourceFileManager
	 */
	public function createSourceFileManager() ;
	
	/**
	 * return CompilerManager
	 */
	public function createCompilerManager() ;
		
	/**
	 * return InterpreterManager
	 */
	public function createInterpreterManager() ;	
}

?>