<?php
namespace org\jecat\framework\ui\xhtml\weave ;

use org\jecat\framework\ui\xhtml\compiler\BaseCompiler;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;

class WeaveCompiler extends BaseCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\weave\\WeaveinObject",$aObject,'aObject') ;
		$aDev->write( $aObject->compiled() ) ;
	}
	
	public function compileStrategySignture()
	{
		return WeaveManager::singleton()->compileStrategySignture() ;
	}
}

?>