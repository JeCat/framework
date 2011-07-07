<?php
namespace jc\ui\xhtml ;

use jc\ui\xhtml\parsers\ParserStateMacro;
use jc\ui\xhtml\compiler\MacroCompiler;
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
		
		ParserStateMacro::singleton()
				->addMacroType('?')
				->addMacroType('=')
				->addMacroType('/')
				->addMacroType('*') ;

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
		$aCompilers->add(__NAMESPACE__.'\\Node',$this->createNodeCompiler()) ;
		$aCompilers->add(__NAMESPACE__.'\\Macro',$this->createMacroCompiler()) ;

		return $aCompilers ;
	}
	
	public function createNodeCompiler()
	{
		// Node Compiler
		if( !$aNodeCompiler=NodeCompiler::singleton(false) )
		{
			$aNodeCompiler=NodeCompiler::singleton(true) ;
			
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
		}
		
		return $aNodeCompiler ;
	}

	public function createMacroCompiler()
	{
		// Node Compiler
		if( !$aMacroCompiler=MacroCompiler::singleton(false) )
		{
			$aMacroCompiler=MacroCompiler::singleton(true) ;

			// 
			$aMacroCompiler->addSubCompiler('*',__NAMESPACE__."\\compiler\\macro\\CommentMacroCompiler") ;
			$aMacroCompiler->addSubCompiler('?',__NAMESPACE__."\\compiler\\macro\\EvalMacroCompiler") ;
			$aMacroCompiler->addSubCompiler('=',__NAMESPACE__."\\compiler\\macro\\PrintMacroCompiler") ;
			$aMacroCompiler->addSubCompiler('/',__NAMESPACE__."\\compiler\\macro\\PathMacroCompiler") ;
		}
		
		return $aMacroCompiler ;
	}
}

?>