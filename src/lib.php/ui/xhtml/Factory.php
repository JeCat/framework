<?php

namespace jc\ui\xhtml ;

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
	 * return IDisplayDevice
	 */
	public function createDisplayDevice()
	{
		return new StreamDisplayDevice() ;
	}
	
	
	static protected $aGlobalInstance ;
}

?>