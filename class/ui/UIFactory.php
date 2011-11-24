<?php
namespace org\jecat\framework\ui ;

use org\jecat\framework\util\HashTable;
use org\jecat\framework\util\DataSrc;
use org\jecat\framework\lang\Object as JeObject ;

abstract class UIFactory extends JeObject implements IFactory 
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
	 * return SourceFileManager
	 */
	public function sourceFileManager()
	{
		if(!$this->aSourceFileManager)
		{
			$this->aSourceFileManager = $this->newSourceFileManager() ;
		}
		
		return $this->aSourceFileManager ;
	}
	/**
	 * @return SourceFileManager
	 */
	public function newSourceFileManager()
	{
		$aSourceFileManager = SourceFileManager::singleton(true) ;
		$aSourceFileManager->setApplication($this->application(true)) ;
		
		return $aSourceFileManager ;
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
	 * return CompilerManager
	 */
	public function compilerManager()
	{
		if( !$this->aCompilers )
		{
			$this->aCompilers = $this->newCompilerManager() ;
		}
		return $this->aCompilers ;
	}
	/**
	 * @return CompilerManager
	 */
	public function newCompilerManager()
	{
		$aCompilers = CompilerManager::singleton(true) ;
		$aCompilers->setApplication($this->application(true)) ;
		
		return $aCompilers ;
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
	 * return InterpreterManager
	 */
	public function interpreterManager()
	{
		if( !$this->aInterpreters )
		{
			$this->aInterpreters = $this->newInterpreterManager() ;
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