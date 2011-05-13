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
			$aCompilers->add(__NAMESPACE__.'\\ObjectBase',__NAMESPACE__.'\\compiler\\BaseCompiler') ;
			$aCompilers->add(__NAMESPACE__.'\\Mark',__NAMESPACE__.'\\compiler\\MarkCompiler') ;
			
			// Node Compiler
			$aNodeCompiler = NodeCompiler::singleton() ;
			$aCompilers->add(__NAMESPACE__.'\\Node',$aNodeCompiler) ;
			
			$aNodeCompiler->addSubCompiler('if',__NAMESPACE__."\\compiler\\node\\IfCompiler") ;
			$aNodeCompiler->addSubCompiler('else',__NAMESPACE__."\\compiler\\node\\ElseCompiler") ;
			$aNodeCompiler->addSubCompiler('elseif',__NAMESPACE__."\\compiler\\node\\ElseIfCompiler") ;
			$aNodeCompiler->addSubCompiler('for',__NAMESPACE__."\\compiler\\node\\ForCompiler") ;
			$aNodeCompiler->addSubCompiler('loop',__NAMESPACE__."\\compiler\\node\\LoopCompiler") ;
			$aNodeCompiler->addSubCompiler('while',__NAMESPACE__."\\compiler\\node\\WhileCompiler") ;
			$aNodeCompiler->addSubCompiler('include',__NAMESPACE__."\\compiler\\node\\IncludeCompiler") ;
			$aNodeCompiler->addSubCompiler('function',__NAMESPACE__."\\compiler\\node\\FunctionCompiler") ;
			$aNodeCompiler->addSubCompiler('continue',__NAMESPACE__."\\compiler\\node\\ContinueCompiler") ;
			$aNodeCompiler->addSubCompiler('break',__NAMESPACE__."\\compiler\\node\\BreakCompiler") ;
			
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