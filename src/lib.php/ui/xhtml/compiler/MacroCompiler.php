<?php
namespace jc\ui\xhtml\compiler ;

use jc\lang\Assert;
use jc\io\IOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;

class MacroCompiler extends BaseCompiler
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
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