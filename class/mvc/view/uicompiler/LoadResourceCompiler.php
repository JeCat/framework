<?php
namespace org\jecat\framework\mvc\view\uicompiler ;

use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

class LoadResourceCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$aDev->write("\$theView = \$aVariables->get('theView') ;\r\n") ;
		$aDev->write("\$aDevice->write(\\org\\jecat\\framework\\resrc\\HtmlResourcePool::singleton()) ;\r\n") ;
	}
}

?>