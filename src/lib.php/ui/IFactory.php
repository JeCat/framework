<?php
namespace jc\ui ;

interface IFactory
{
	/**
	 * return IUI
	 */
	public function create() ;
	
	/**
	 * return ICompiler
	 */
	public function createSourceFileManager() ;
	
	/**
	 * return IUI
	 */
	public function createUI() ;
	
	/**
	 * return ICompiler
	 */
	public function createCompiler() ;
		
	/**
	 * return IInterpreter
	 */
	public function createInterpreter() ;
	
	/**
	 * return IDisplayDevice
	 */
	public function createDisplayDevice() ;
}

?>