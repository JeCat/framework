<?php

namespace jc\ui\xhtml ;

use jc\ui\xhtml\compiler\NodeCompiler;

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
			
			// 注册 compiler
			$aCompilers->add(__NAMESPACE__.'\\Mark',__NAMESPACE__.'\\compiler\\Mark') ;
			$aCompilers->add(__NAMESPACE__.'\\ObjectBase',__NAMESPACE__.'\\compiler\\Mark') ;
			
			// Node Compiler
			$aNodeCompiler = NodeCompiler::singleton() ;
			$aCompilers->add(__NAMESPACE__.'\\Node',$aNodeCompiler) ;
			
			$aNodeCompiler->addSubCompiler('if',__NAMESPACE__."\\compiler\\IfNodeCompiler") ;
			$aNodeCompiler->addSubCompiler('for',__NAMESPACE__."\\compiler\\ForNodeCompiler") ;
			
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