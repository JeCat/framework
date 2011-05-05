<?php

namespace jc\ui ;

use jc\util\DataSrc;

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
	 * return IInterpreter
	 */
	public function interpreter()
	{
		return $this->aInterpreter ;
	}
	
	public function setInterpreter(IInterpreter $aInterpreter)
	{
		$this->aInterpreter = $aInterpreter ;
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

		if( !$this->sourceFileManager()->isCompiledValid($sSourcePath,$sCompiledPath) )
		{
			// 解析
			$aObjectTree = $this->interpreter()->parse($sSourcePath) ;
			
			// 编译
			return $this->compiler()->compile($aObjectTree,$sCompiledPath) ;
		}
		else 
		{
			// 载入
			return $this->compiler()->loadCompiled($sCompiledPath) ;
		}
	}
	
	public function display($sSourceFile,IHashTable $aVariables=null,IDisplayer $aDisplayDevice=null)
	{		
		$aCompiled = $this->compile($sSourceFile) ;
		
		if($aCompiled)
		{
			if(!$aVariables)
			{
				$aVariables = $this->variables() ;
			}
			
			if(!$aDisplayDevice)
			{
				$aDisplayDevice = $this->displayDevice() ;
			}
			
			$aVariables->set('aUI',$this) ;
			$aDisplayDevice->render($aCompiled,$aVariables) ;
		}
	}
	
	private $aSourceFileManager ;
	
	private $aCompiler ;
	
	private $aDisplayDevice ;
	
	private $aVariables ;

	private $aInterpreter ;
}

?>