<?php
namespace jc\mvc\uicompiler ;

use jc\lang\Type;
use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\io\IOutputStream;
use jc\ui\xhtml\compiler\NodeCompiler;

class ViewCompiler extends NodeCompiler
{

	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check("jc\\ui\\xhtml\\Node",$aObject) ;

		$aDev->write("<?php ob_flush() ;\r\n") ;
		$aDev->write("\$aViewContainer = new \\jc\\mvc\\ViewContainer() ;\r\n") ;
		$aDev->write("\$aVariables->get('aView')->outputStream()->write(\$aViewContainer->outputStream());\r\n") ;
		$aDev->write("?>") ;
	}
}

?>