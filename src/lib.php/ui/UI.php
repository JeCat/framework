<?php

namespace jc\ui ;

use jc\io\IOutputStream;
use jc\util\DataSrc;
use jc\lang\Object as JcObject;
use jc\util\IHashTable;

class UI extends JcObject
{
	public function __construct(IFactory $aFactory)
	{
		$this->setFactory($aFactory) ;
	}
	
	/**
	 * return IFactory
	 */
	public function factory()
	{
		return $this->aFactory ;
	}
	
	public function setFactory(IFactory $aFactory)
	{
		$this->aFactory = $aFactory ;
	}
	
	/**
	 * return SourceFolderManager
	 */
	public function sourceFileManager()
	{
		if(!$this->aSourceFileManager)
		{
			$this->aSourceFileManager = $this->aFactory->createSourceFileManager() ;
		}
		return $this->aSourceFileManager ;
	}
	
	public function setSourceFileManager(SourceFileManager $aSrcMgr)
	{
		$this->aSourceFileManager = $aSrcMgr ;
	}
	
	/**
	 * return CompilerManager
	 */
	public function compilers()
	{
		if(!$this->aCompilers)
		{
			$this->aCompilers = $this->aFactory->createCompilerManager() ;
		}
		return $this->aCompilers ;
	}
	
	public function setCompilers(CompilerManager $aCompilers)
	{
		$this->aCompilers = $aCompilers ;
	}
	
	/**
	 * return InterpreterManager
	 */
	public function interpreters()
	{
		if(!$this->aInterpreters)
		{
			$this->aInterpreters = $this->aFactory->createInterpreterManager() ;
		}
		return $this->aInterpreters ;
	}
	
	public function setInterpreters(InterpreterManager $aInterpreters)
	{
		$this->aInterpreters = $aInterpreters ;
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
	
	public function compile($sCompiledPath,$sSourceFile)
	{
		// 解析
		$aObjectTree = $this->interpreters()->parse($sSourcePath) ;
		
		// 编译
		$this->compilers()->compile($aObjectTree,$sCompiledPath) ;
	}
	
	public function render($sCompiledPath)
	{
		
	}
	
	public function display($sSourceFile,IHashTable $aVariables=null,IOutputStream $aDevice=null)
	{
		// 编译
		$sSourcePath = $this->sourceFileManager()->find($sSourceFile) ;
		$sCompiledPath = $this->sourceFileManager()->compiledPath($sSourcePath) ;
		if( !$this->sourceFileManager()->isCompiledValid($sSourcePath,$sCompiledPath) )
		{
			$this->compile($sSourceFile) ;
		}
		
		// render
		if(!$aVariables)
		{
			$aVariables = $this->variables() ;
		}
		
		if(!$aDevice)
		{
			$aDevice = $this->application()->response()->printer() ;
		}
		
		$aVariables->set('aUI',$this) ;
		// $aDisplayDevice->render($aCompiled,$aVariables) ;
	}
	
	private $aSourceFileManager ;
	
	private $aCompilers ;
	
	private $aVariables ;

	private $aInterpreters ;
}

?>