<?php
namespace jc\ui\xhtml\weave ;

use jc\ui\xhtml\compiler\BaseCompiler;
use jc\lang\Assert;
use jc\ui\TargetCodeOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;

class WeaveCompiler extends BaseCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("jc\\ui\\xhtml\\weave\\WeaveinObject",$aObject,'aObject') ;
		$aDev->write( $aObject->compiled() ) ;
	}
}

?>