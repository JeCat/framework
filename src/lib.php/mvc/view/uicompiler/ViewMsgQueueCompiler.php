<?php
namespace jc\mvc\view\uicompiler ;

use jc\lang\Assert;
use jc\lang\Exception;
use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\ui\TargetCodeOutputStream;
use jc\ui\xhtml\compiler\NodeCompiler;

class ViewMsgQueueCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("jc\\ui\\xhtml\\Node",$aObject,'aObject') ;
		
		$aDev->write("if( \$aVariables->get('theView')->messageQueue()->count() ){ \r\n") ;
		$aDev->write("	\$aVariables->get('theView')->messageQueue()->display(\$this,\$aDevice) ;\r\n") ;
		$aDev->write("}\r\n") ;
	}
}

?>