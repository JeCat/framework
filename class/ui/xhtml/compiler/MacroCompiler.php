<?php
namespace org\jecat\framework\ui\xhtml\compiler ;

use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;

class MacroCompiler extends BaseCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Macro",$aObject,'aObject') ;
		
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