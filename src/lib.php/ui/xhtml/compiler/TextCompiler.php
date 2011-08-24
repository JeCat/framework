<?php
namespace jc\ui\xhtml\compiler ;

use jc\lang\Assert;
use jc\ui\TargetCodeOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;

class TextCompiler extends BaseCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		if( $aObject instanceof \jc\ui\xhtml\ObjectBase and !$aObject->count() )
		{
			Assert::type("jc\\ui\\xhtml\\Text",$aObject,'aObject') ;

			$aDev->output($aObject->source()) ;
		}
		
		else 
		{
			$this->compileChildren($aObject,$aDev,$aCompilerManager) ;
		}
	}
}

?>