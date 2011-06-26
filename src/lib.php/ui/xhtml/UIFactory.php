<?php

namespace jc\ui\xhtml ;

use jc\ui\xhtml\compiler\NodeCompiler;
use jc\ui\xhtml\nodes\TagLibrary;
use jc\ui\UIFactory as UIFactoryBase ;

class UIFactory extends UIFactoryBase 
{	
	/**
	 * return SourceFileManager
	 */
	public function newInterpreterManager()
	{
		$aInterpreters = parent::newInterpreterManager() ;
		
		// 注册  parser
		$aInterpreters->add(parsers\Parser::singleton()) ;

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
		$aCompilers->add(__NAMESPACE__.'\\Macro',__NAMESPACE__.'\\compiler\\MacroCompiler') ;
		$aCompilers->add(__NAMESPACE__.'\\Node',$this->createNodeCompiler()) ;
			
		return $aCompilers ;
	}
	
	public function createNodeCompiler()
	{
		// Node Compiler
		$aNodeCompiler = NodeCompiler::singleton() ;
		//if
		$aNodeCompiler->addSubCompiler('if',__NAMESPACE__."\\compiler\\node\\IfCompiler") ;
		$aNodeCompiler->addSubCompiler('else',__NAMESPACE__."\\compiler\\node\\ElseCompiler") ;
		$aNodeCompiler->addSubCompiler('if:else',__NAMESPACE__."\\compiler\\node\\ElseCompiler") ;
		$aNodeCompiler->addSubCompiler('elseif',__NAMESPACE__."\\compiler\\node\\ElseIfCompiler") ;
		//for
		$aNodeCompiler->addSubCompiler('loop',__NAMESPACE__."\\compiler\\node\\LoopCompiler") ;
		//foreach
		$aNodeCompiler->addSubCompiler('foreach',__NAMESPACE__."\\compiler\\node\\ForeachCompiler") ;
		$aNodeCompiler->addSubCompiler('foreach:else',__NAMESPACE__."\\compiler\\node\\ForeachelseCompiler") ;
		//while
		$aNodeCompiler->addSubCompiler('while',__NAMESPACE__."\\compiler\\node\\WhileCompiler") ;
		$aNodeCompiler->addSubCompiler('dowhile',__NAMESPACE__."\\compiler\\node\\DoWhileCompiler") ;
		$aNodeCompiler->addSubCompiler('do',__NAMESPACE__."\\compiler\\node\\DoWhileCompiler") ;
		//ends
		$aNodeCompiler->addSubCompiler('signle:end',__NAMESPACE__."\\compiler\\node\\SingleEndCompiler") ;
		$aNodeCompiler->addSubCompiler('if:end',__NAMESPACE__."\\compiler\\node\\SingleEndCompiler") ;
		$aNodeCompiler->addSubCompiler('loop:end',__NAMESPACE__."\\compiler\\node\\SingleEndCompiler") ;
		$aNodeCompiler->addSubCompiler('while:end',__NAMESPACE__."\\compiler\\node\\SingleEndCompiler") ;
		$aNodeCompiler->addSubCompiler('dowhile:end',__NAMESPACE__."\\compiler\\node\\SingleEndCompiler") ;
		$aNodeCompiler->addSubCompiler('double:end',__NAMESPACE__."\\compiler\\node\\DoubleEndCompiler") ;
		$aNodeCompiler->addSubCompiler('foreach:end',__NAMESPACE__."\\compiler\\node\\DoubleEndCompiler") ;
		//others
		$aNodeCompiler->addSubCompiler('include',__NAMESPACE__."\\compiler\\node\\IncludeCompiler") ;
		$aNodeCompiler->addSubCompiler('function',__NAMESPACE__."\\compiler\\node\\FunctionCompiler") ;
		$aNodeCompiler->addSubCompiler('continue',__NAMESPACE__."\\compiler\\node\\ContinueCompiler") ;
		$aNodeCompiler->addSubCompiler('break',__NAMESPACE__."\\compiler\\node\\BreakCompiler") ;
		$aNodeCompiler->addSubCompiler('script',__NAMESPACE__."\\compiler\\node\\ScriptCompiler") ;
		
		$aNodeCompiler->addSubCompiler('subtemplate',__NAMESPACE__."\\compiler\\node\\SubTemplateDefineCompiler") ;
		$aNodeCompiler->addSubCompiler('subtemplate:define',__NAMESPACE__."\\compiler\\node\\SubTemplateDefineCompiler") ;
		$aNodeCompiler->addSubCompiler('subtemplate:call',__NAMESPACE__."\\compiler\\node\\SubTemplateCallCompiler") ;
		
		return $aNodeCompiler ;
	}
	
}

?>