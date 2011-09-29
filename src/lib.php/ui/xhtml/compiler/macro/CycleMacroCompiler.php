<?php
namespace jc\ui\xhtml\compiler\macro ;

use jc\ui\xhtml\compiler\MacroCompiler ;
use jc\ui\TargetCodeOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;

class CycleMacroCompiler extends MacroCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$sSource = $aObject->source() ;
		
		
		$aDev->write("\$aDevice->write('this is Cycle Macro <br />');") ;
		
		$aDev->output("macro's content is :{$sSource}") ;
	}
}
