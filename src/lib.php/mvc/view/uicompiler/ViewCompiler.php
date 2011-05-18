<?php
namespace jc\mvc\view\uicompiler ;

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

		$aAttrs = $aObject->attributes() ;
		
		if( $aAttrs->has('for') )
		{
			$sFor = $aAttrs->expression('for') ;
		}
		else 
		{
			$sFor = "(\$__aController=\$aVariables->get('theController'))? \$__aController->mainView(): \$theView" ;
		}
		
		$aDev->write("<?php ob_flush() ;\r\n") ;
		$aDev->write("\$theView = \$aVariables->get('theView') ;\r\n") ;
		$aDev->write("\$__aViewContainer = new \\jc\\mvc\\view\\View() ;\r\n") ;
		$aDev->write("\$__aViewContainer->addName('ViewContainer') ;\r\n") ;
		$aDev->write("\$theView->outputStream()->write(\$__aViewContainer->outputStream());\r\n") ;
		$aDev->write("\$__aSearchFor = {$sFor};\r\n") ;
		$aDev->write("if(\$__aSearchFor){\r\n") ;
		$aDev->write("\tforeach(new \\jc\\mvc\\view\\VagrantViewSearcher(\$__aSearchFor) as \$__aVagrantView){\r\n") ;
		$aDev->write("\t\tif(\$__aVagrantView!=\$theView){\r\n") ;
		$aDev->write("\t\t\t\$__aViewContainer->add(\$__aVagrantView,true) ;\r\n") ;
		$aDev->write("\t\t}\r\n") ;
		$aDev->write("\t}\r\n") ;
		$aDev->write("}?>") ;
	}
	
	static public function FindVagrantView()
	{
		
	}
}

?>