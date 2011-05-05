<?php

namespace jc\ui ;

use jc\util\IHashTable;

interface IUI
{
	/**
	 * return SourceFolderManager
	 */
	public function sourceFileManager() ;
	
	public function setSourceFileManager(SourceFileManager $aSrcMgr) ;
	
	/**
	 * return IInterpreter
	 */
	public function interpreter() ;
	
	public function setInterpreter(IInterpreter $aInterpreter) ;
	
	/**
	 * return ICompiler
	 */
	public function compiler() ;
	
	public function setCompiler(ICompiler $aCompiler) ;
	
	/**
	 * return IDisplayDevice
	 */
	public function displayDevice() ;
	
	public function setDisplayDevice(IDisplayDevice $aDisplayDevice) ;
	
	/**
	 * @return IHashTable
	 */
	public function variables() ;
	
	public function setVariables(IHashTable $aVariables) ;
	
	/**
	 * return IObject
	 */
	public function compile($sSourceFile) ;
	
	public function display($sSourceFile,IDataSrc $aVariables=null,IDisplayer $aDisplayDevice=null) ;
}

?>