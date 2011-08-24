<?php
namespace jc\mvc\view\uicompiler ;

use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\ui\TargetCodeOutputStream;
use jc\ui\xhtml\compiler\NodeCompiler;

class LoadResourceCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$aDev->write("\$theView = \$aVariables->get('theView') ;\r\n") ;
		$aDev->write("\$aDevice->write(\\jc\\resrc\\HtmlResourcePool::singleton()) ;\r\n") ;
	}
}

?>