<?php
namespace jc\mvc\view\uicompiler ;

use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\io\IOutputStream;
use jc\ui\xhtml\compiler\NodeCompiler;

class LoadResourceCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$aDev->write("<?php \r\n") ;
		$aDev->write("ob_flush() ;\r\n") ;
		$aDev->write("\$theView = \$aVariables->get('theView') ;\r\n") ;
		$aDev->write("\$theView->outputStream()->write(\\jc\\resrc\\HtmlResourcePool::singleton()) ;\r\n") ;
		$aDev->write("?>") ;
	}
}

?>