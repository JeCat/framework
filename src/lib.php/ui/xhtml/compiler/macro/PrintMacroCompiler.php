<?php
namespace jc\ui\xhtml\compiler\macro ;

use jc\ui\xhtml\compiler\ExpressionCompiler;
use jc\ui\xhtml\compiler\MacroCompiler ;
use jc\io\IOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;

class PrintMacroCompiler extends MacroCompiler
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$aDev->write( "<?php echo " . ExpressionCompiler::compileExpression($aObject->source()) . " ;?>" ) ;
	}
}

?>