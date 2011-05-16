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
		if( !$this->aCompilers )
		{
			$this->aCompilers = newCompilerManager() ;
		}
		return $this->aCompilers ;
	}
	/**
	 * @return CompilerManager
	 */
	public function newCompilerManager()
	{
			$this->aCompilers = CompilerManager::singleton(true) ;
			$this->aCompilers->setApplication($this->application(true)) ;
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
		if( !$this->aInterpreters )
		{
			$this->aInterpreters = new $this->newInterpreterManager() ;
		}
		return $this->aInterpreters ;
	}
	/**
	 * @return InterpreterManager
	 */
	public function newInterpreterManager()
	{
		$aInterpreters = InterpreterManager::singleton(true) ;
		$aInterpreters->setApplication($this->application(true)) ;

		return $aInterpreters ;
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