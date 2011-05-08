<?php

namespace jc\ui\xhtml ;

use jc\ui\xhtml\nodes\TagLibrary;
use jc\ui\FactoryBase;

class Factory extends FactoryBase 
{	
	/**
	 * return SourceFileManager
	 */
	public function interpreterManager()
	{
		if(!$this->aInterpreters)
		{
			$aInterpreters = parent::interpreterManager() ;
			
			// 注册  parser
			$aInterpreters->add(parsers\NodeParser::singleton()) ;
			$aInterpreters->add(parsers\MarkParser::singleton()) ;
			$aInterpreters->add(parsers\TreeBuilder::singleton()) ;
		}
		
		return $this->aInterpreters ;
	}
	
	/**
	 * return SourceFileManager
	 */
	public function compilerManager()
	{
		if(!$this->aCompilers)
		{
			$aCompilers = parent::compilerManager() ;
			
		}
		
		return $this->aCompilers ;
	}
	
	/**
	 * return IDisplayDevice
	 */
	public function createDisplayDevice()
	{
		return new StreamDisplayDevice() ;
	}
}

?>