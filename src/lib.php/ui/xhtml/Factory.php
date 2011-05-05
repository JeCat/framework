<?php

namespace jc\ui\xhtml ;

use jc\ui\xhtml\nodes\TagLibrary;
use jc\ui\FactoryBase;

class Factory extends FactoryBase 
{	
	/**
	 * return ICompiler
	 */
	public function createCompiler()
	{
		return new Compiler() ;
	}
	
	/**
	 * return ICompiler
	 */
	public function createInterpreter()
	{
		$aInterpreter = new Interpreter() ;
		$aInterpreter->setTagLibrary($this->tagLibrary()) ;
		
		return $aInterpreter ;
	}
	
	/**
	 * return IDisplayDevice
	 */
	public function createDisplayDevice()
	{
		return new StreamDisplayDevice() ;
	}
	
	/**
	 * @return TagLibrary
	 */
	public function tagLibrary()
	{
		if( !$this->aTagLibrary )
		{
			$this->aTagLibrary = new TagLibrary() ;
		}
		return $this->aTagLibrary ;
	}
	
	public function setTagLibrary(TagLibrary $aTagLibrary)
	{
		$this->aTagLibrary = $aTagLibrary ;
	}
		
	private $aTagLibrary ;
	
	static protected $aGlobalInstance ;
}

?>