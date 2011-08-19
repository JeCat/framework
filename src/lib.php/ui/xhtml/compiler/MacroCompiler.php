<?php
namespace jc\ui\xhtml\compiler ;

use jc\lang\Assert;
use jc\ui\TargetCodeOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;

class MacroCompiler extends BaseCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("jc\\ui\\xhtml\\Macro",$aObject,'aObject') ;
		
		if( $aCompiler=$this->subCompiler($aObject->macroType()) )
		{
			$aCompiler->compile($aObject,$aDev,$aCompilerManager) ;
		}
		
		else 
		{
			$aDev->write( $aObject->source() ) ;
		}
		
	}
}

?>