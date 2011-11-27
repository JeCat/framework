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
		return new UI($this) ;
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
		return SourceFileManager::singleton(true) ;
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
		return CompilerManager::singleton(true) ;
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
		return InterpreterManager::singleton(true) ;
	}
	public function setInterpreter(InterpreterManager $aInterpreters)
	{
		$this->aInterpreters = $aInterpreters ;
	}

	public function calculateCompileStrategySignture()
	{
		$sSignture = md5( $this->interpreterManager()->compileStrategySignture()
							. $this->compilerManager()->compileStrategySignture() ) ;
		
		$this->sourceFileManager()->setCompileStrategySignture($sSignture) ;
	}
	
	protected $aSourceFileManager ;
	protected $aCompilers ;
	protected $aInterpreters ;
}

?>