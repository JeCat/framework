<?php
namespace org\jecat\framework\ui\xhtml\compiler ;

use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\ObjectContainer;

class TextCompiler extends BaseCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		if( $aObject instanceof \org\jecat\framework\ui\xhtml\ObjectBase and !$aObject->count() )
		{
			Assert::type("org\\jecat\\framework\\ui\\xhtml\\Text",$aObject,'aObject') ;

			$aDev->output($aObject->source()) ;
		}
		
		else 
		{
			$this->compileChildren($aObject,$aObjectContainer,$aDev,$aCompilerManager) ;
		}
	}
}

?>