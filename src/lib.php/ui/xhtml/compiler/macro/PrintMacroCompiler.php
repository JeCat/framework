<?php
namespace jc\ui\xhtml\compiler\macro ;

use jc\ui\xhtml\compiler\ExpressionCompiler;
use jc\ui\xhtml\compiler\MacroCompiler ;
use jc\ui\TargetCodeOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;

class PrintMacroCompiler extends MacroCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$aDev->write( "echo " . ExpressionCompiler::compileExpression($aObject->source()) . " ;" ) ;
	}
}

?>