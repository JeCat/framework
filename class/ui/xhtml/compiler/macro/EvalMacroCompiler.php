<?php
namespace org\jecat\framework\ui\xhtml\compiler\macro ;

use org\jecat\framework\ui\xhtml\compiler\ExpressionCompiler;
use org\jecat\framework\ui\xhtml\compiler\MacroCompiler ;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\ObjectContainer;

class EvalMacroCompiler extends MacroCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$aDev->write( ExpressionCompiler::compileExpression($aObject->source(),$aObjectContainer->variableDeclares(),false,true) ) ;
	}
}

?>