<?php
namespace org\jecat\framework\ui\xhtml\compiler\macro ;

use org\jecat\framework\ui\xhtml\compiler\ExpressionCompiler;
use org\jecat\framework\ui\xhtml\compiler\MacroCompiler ;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\ObjectContainer;

/**
 * @wiki /模板引擎/宏
 * @wiki 速查/模板引擎/宏
 * =={? }==
 *
 *  {? }可以使用宏内的表达式或者value
 * {|
 *  !使用方法
 *  !
 *  !说明
 *  !
 *  !
 *  |---
 *  |{? 变量/表达式}
 *  |
 *  |
 *  |
 *  |
 *  |}
 *  [example php frameworktest template/test-template/macro/EvalMacroCase.html 1 2]
 */

class EvalMacroCompiler extends MacroCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$aDev->write( ExpressionCompiler::compileExpression($aObject->source(),$aObjectContainer->variableDeclares(),false,true).';' ) ;
	}
}

?>