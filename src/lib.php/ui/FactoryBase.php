<?php
namespace jc\ui ;

use jc\util\HashTable;
use jc\util\DataSrc;
use jc\lang\Object;

abstract class FactoryBase extends Object implements IFactory 
{
	/**
	 * return UI
	 */
	public function create()
	{
		$aUI = new UI($this) ;
		$aUI->setApplication($this->application(true)) ;

		return $aUI ;
	}

	/**
	 * return SourceFileManager
	 */
	public function createSourceFileManager()
	{
		return $this->sourceFileManager() ;
	}
	/**
	 * @return SourceFileManager
	 */
	public function sourceFileManager()
	{
		if(!$this->aSourceFileManager)
		{
			$this->aSourceFileManager = SourceFileManager::singleton(true) ;
			$this->aSourceFileManager->setApplication($this->application(true)) ;
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
	public function createCompilerManager()
	{
		return $this->compilerManager() ;
	}
	/**
	 * @return CompilerManager
	 */
	public function compilerManager()
	{
		if( !$this->aCompilers )
		{
			$this->aCompilers = CompilerManager::singleton(true) ;
			$this->aCompilers->setApplication($this->application(true)) ;
		}
		return $this->aCompilers ;
	}
	public function setCompilerManager(CompilerManager $aCompilers)
	{
		$this->aCompilers = $aCompilers ;
	}
	
	/**
	 * return InterpreterManager
	 */
	public function createInterpreterManager()
	{
		return $this->interpreterManager() ;
	}
	/**
	 * @return InterpreterManager
	 */
	public function interpreterManager()
	{
		if( !$this->aInterpreters )
		{
			$this->aInterpreters = InterpreterManager::singleton(true) ;
			$this->aInterpreters->setApplication($this->application(true)) ;
		}
		return $this->aInterpreters ;
	}
	public function setInterpreter(InterpreterManager $aInterpreters)
	{
		$this->aInterpreters = $aInterpreters ;
	}
	
	protected $aSourceFileManager ;
	protected $aCompilers ;
	protected $aInterpreters ;
}

?>