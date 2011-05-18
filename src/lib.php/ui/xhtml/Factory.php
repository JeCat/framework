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
	public function newInterpreterManager()
	{
		$aInterpreters = parent::newInterpreterManager() ;
		
		// 注册  parser
		$aInterpreters->add(parsers\NodeParser::singleton()) ;
		$aInterpreters->add(parsers\MarkParser::singleton()) ;

		
		return $aInterpreters ;
	}
	
	/**
	 * return SourceFileManager
	 */
	public function newCompilerManager()
	{
		$aCompilers = parent::newCompilerManager() ;
		
		// 注册 compiler
		$aCompilers->add(__NAMESPACE__.'\\ObjectBase',__NAMESPACE__.'\\compiler\\BaseCompiler') ;
		$aCompilers->add(__NAMESPACE__.'\\Mark',__NAMESPACE__.'\\compiler\\MarkCompiler') ;
		$aCompilers->add(__NAMESPACE__.'\\Node',$this->createNodeCompiler()) ;
			
		return $aCompilers ;
	}
	
	public function createNodeCompiler()
	{
		// Node Compiler
		$aNodeCompiler = NodeCompiler::singleton() ;
		
		$aNodeCompiler->addSubCompiler('if',__NAMESPACE__."\\compiler\\node\\IfCompiler") ;
		$aNodeCompiler->addSubCompiler('else',__NAMESPACE__."\\compiler\\node\\ElseCompiler") ;
		$aNodeCompiler->addSubCompiler('elseif',__NAMESPACE__."\\compiler\\node\\ElseIfCompiler") ;
		$aNodeCompiler->addSubCompiler('foreach',__NAMESPACE__."\\compiler\\node\\ForeachCompiler") ;
		$aNodeCompiler->addSubCompiler('loop',__NAMESPACE__."\\compiler\\node\\LoopCompiler") ;
		$aNodeCompiler->addSubCompiler('while',__NAMESPACE__."\\compiler\\node\\WhileCompiler") ;
		$aNodeCompiler->addSubCompiler('include',__NAMESPACE__."\\compiler\\node\\IncludeCompiler") ;
		$aNodeCompiler->addSubCompiler('function',__NAMESPACE__."\\compiler\\node\\FunctionCompiler") ;
		$aNodeCompiler->addSubCompiler('continue',__NAMESPACE__."\\compiler\\node\\ContinueCompiler") ;
		$aNodeCompiler->addSubCompiler('break',__NAMESPACE__."\\compiler\\node\\BreakCompiler") ;
		
		return $aNodeCompiler ;
	}
	
}

?>