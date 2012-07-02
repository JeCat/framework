<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.8
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\ui\xhtml ;

use org\jecat\framework\ui\xhtml\parsers\ParserStateTag;
use org\jecat\framework\ui\xhtml\parsers\ParserStateMacro;
use org\jecat\framework\ui\xhtml\compiler\MacroCompiler;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\UIFactory as UIFactoryBase;

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
		$aInterpreters->add(weave\WeaveParser::singleton()) ;
		
		ParserStateMacro::singleton()
				->addMacroType('?')
				->addMacroType('=')
				->addMacroType('/')
				->addMacroType('*')
				->addMacroType('@') ;

		// for ui
		ParserStateTag::singleton()->addTagNames(
				'if', 'else', 'if:else', 'elseif', 'loop', 'foreach', 'foreach:else','while:else','dowhile:else','do:else','loop:else' ,'while', 'dowhile', 'do', 'struct:end'
				, 'if:end', 'loop:end', 'while:end', 'dowhile:end', 'foreach:end'
				, 'include', 'function', 'continue', 'break', 'script'
				, 'subtemplate', 'subtemplate:define', 'subtemplate:call'
				, 'nl', 'clear', 'code', 'render:js'
		) ;
		
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
		$aCompilers->add(__NAMESPACE__.'\\weave\\WeaveinObject',__NAMESPACE__.'\\weave\\WeaveCompiler') ;
		$aCompilers->add(__NAMESPACE__.'\\Text',__NAMESPACE__.'\\compiler\\TextCompiler') ;
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
			$aNodeCompiler->addSubCompiler('for',__NAMESPACE__."\\compiler\\node\\LoopCompiler") ;
			$aNodeCompiler->addSubCompiler('loop',__NAMESPACE__."\\compiler\\node\\LoopCompiler") ;
			//foreach
			$aNodeCompiler->addSubCompiler('foreach',__NAMESPACE__."\\compiler\\node\\ForeachCompiler") ;
			//loopelse
			$aNodeCompiler->addSubCompiler('foreach:else',__NAMESPACE__."\\compiler\\node\\LoopElseCompiler") ;
			$aNodeCompiler->addSubCompiler('while:else',__NAMESPACE__."\\compiler\\node\\LoopElseCompiler") ;
			$aNodeCompiler->addSubCompiler('dowhile:else',__NAMESPACE__."\\compiler\\node\\LoopElseCompiler") ;
			$aNodeCompiler->addSubCompiler('do:else',__NAMESPACE__."\\compiler\\node\\LoopElseCompiler") ;
			$aNodeCompiler->addSubCompiler('loop:else',__NAMESPACE__."\\compiler\\node\\LoopElseCompiler") ;
			//while
			$aNodeCompiler->addSubCompiler('while',__NAMESPACE__."\\compiler\\node\\WhileCompiler") ;
			$aNodeCompiler->addSubCompiler('dowhile',__NAMESPACE__."\\compiler\\node\\DoWhileCompiler") ;
			$aNodeCompiler->addSubCompiler('do',__NAMESPACE__."\\compiler\\node\\DoWhileCompiler") ;
			//ends
			$aNodeCompiler->addSubCompiler('struct:end',__NAMESPACE__."\\compiler\\node\\StructEndCompiler") ;
			$aNodeCompiler->addSubCompiler('if:end',__NAMESPACE__."\\compiler\\node\\StructEndCompiler") ;
			$aNodeCompiler->addSubCompiler('loop:end',__NAMESPACE__."\\compiler\\node\\LoopEndCompiler") ;
			$aNodeCompiler->addSubCompiler('while:end',__NAMESPACE__."\\compiler\\node\\LoopEndCompiler") ;
			$aNodeCompiler->addSubCompiler('dowhile:end',__NAMESPACE__."\\compiler\\node\\LoopEndCompiler") ;
			$aNodeCompiler->addSubCompiler('foreach:end',__NAMESPACE__."\\compiler\\node\\LoopEndCompiler") ;
			//others
			$aNodeCompiler->addSubCompiler('include',__NAMESPACE__."\\compiler\\node\\IncludeCompiler") ;
			$aNodeCompiler->addSubCompiler('function',__NAMESPACE__."\\compiler\\node\\FunctionCompiler") ;
			$aNodeCompiler->addSubCompiler('continue',__NAMESPACE__."\\compiler\\node\\ContinueCompiler") ;
			$aNodeCompiler->addSubCompiler('break',__NAMESPACE__."\\compiler\\node\\BreakCompiler") ;
			$aNodeCompiler->addSubCompiler('script',__NAMESPACE__."\\compiler\\node\\ScriptCompiler") ;
			$aNodeCompiler->addSubCompiler('resrc',__NAMESPACE__."\\compiler\\node\\LoadResourceCompiler") ;
			$aNodeCompiler->addSubCompiler('link',__NAMESPACE__."\\compiler\\node\\CssCompiler") ;
			$aNodeCompiler->addSubCompiler('css',__NAMESPACE__."\\compiler\\node\\CssCompiler") ;
			$aNodeCompiler->addSubCompiler('js',__NAMESPACE__."\\compiler\\node\\ScriptCompiler") ;
			
			$aNodeCompiler->addSubCompiler('subtemplate',__NAMESPACE__."\\compiler\\node\\SubTemplateDefineCompiler") ;
			$aNodeCompiler->addSubCompiler('subtemplate:define',__NAMESPACE__."\\compiler\\node\\SubTemplateDefineCompiler") ;
			$aNodeCompiler->addSubCompiler('subtemplate:call',__NAMESPACE__."\\compiler\\node\\SubTemplateCallCompiler") ;
			$aNodeCompiler->addSubCompiler('template',__NAMESPACE__."\\compiler\\node\\SubTemplateDefineCompiler") ;
			
			$aNodeCompiler->addSubCompiler('nl',__NAMESPACE__."\\compiler\\node\\NlCompiler") ;
			$aNodeCompiler->addSubCompiler('clear',__NAMESPACE__."\\compiler\\node\\ClearCompiler") ;
			
			$aNodeCompiler->addSubCompiler('code',__NAMESPACE__."\\compiler\\node\\CodeCompiler") ;
			$aNodeCompiler->addSubCompiler('render:js',__NAMESPACE__."\\compiler\\node\\RenderJsCompiler") ;
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
			$aMacroCompiler->addSubCompiler('@',__NAMESPACE__."\\compiler\\macro\\CycleMacroCompiler") ;
		}
		
		return $aMacroCompiler ;
	}
	public function newSourceFileManager()
	{
		$aSourceFileManager = parent::newSourceFileManager() ;
		return $aSourceFileManager ;
	}
}

