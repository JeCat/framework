<?php
namespace jc\ui\xhtml\compiler\macro ;

use jc\ui\xhtml\compiler\ExpressionCompiler;
use jc\ui\xhtml\compiler\MacroCompiler ;
use jc\ui\TargetCodeOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;

class EvalMacroCompiler extends MacroCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$aDev->write( ExpressionCompiler::compileExpression($aObject->source(),false,false) ) ;
	}
}

?>