<?php

namespace jc\ui ;

use jc\lang\Object as JcObject;
use jc\util\IHashTable;

class UI extends JcObject
{
	/**
	 * return SourceFolderManager
	 */
	public function sourceFileManager()
	{
		return $this->aSourceFileManager ;
	}
	
	public function setSourceFileManager(SourceFileManager $aSrcMgr)
	{
		$this->aSourceFileManager = $aSrcMgr ;
	}
	
	/**
	 * return ICompiler
	 */
	public function compiler()
	{
		return $this->aCompiler ;
	}
	
	public function setCompiler(ICompiler $aCompiler)
	{
		$this->aCompiler = $aCompiler ;
	}
	
	/**
	 * return IDisplayDevice
	 */
	public function displayDevice()
	{
		return $this->aDisplayDevice ;
	}
	
	public function setDisplayDevice(IDisplayDevice $aDisplayDevice)
	{
		$this->aDisplayDevice = $aDisplayDevice ;
	}
	
	/**
	 * @return IHashTable
	 */
	public function variables()
	{
		return $this->aVariables ;
	}
	
	public function setVariables(IHashTable $aVariables)
	{
		$this->aVariables = $aVariables ;
	}
	
	public function compile($sSourceFile)
	{
		$sSourcePath = $this->sourceFileManager()->find($sSourceFile) ;
		$sCompiledPath = $this->sourceFileManager()->compiledPath($sSourcePath) ;
		
		$this->compiler()->compile($sSourcePath,$sCompiledPath) ;
		
		return $sCompiledPath ;
	}
	
	public function display($sSourceFile,IDataSrc $aVariables=null,IDisplayer $aDisplayDevice=null)
	{
		$aCompiled = $this->compile($sSourceFile) ;
		
		if($aCompiled)
		{
			$aVariables->set('aUI',$this) ;
			$aDisplayDevice->render($aCompiled,$aVariables) ;
		}
	}
	
	private $aSourceFileManager ;
	
	private $aCompiler ;
	
	private $aDisplayDevice ;
	
	private $aVariables ;
}

?>