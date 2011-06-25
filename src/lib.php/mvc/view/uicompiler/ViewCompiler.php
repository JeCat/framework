<?php
namespace jc\mvc\view\uicompiler ;

use jc\lang\Assert;
use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\io\IOutputStream;
use jc\ui\xhtml\compiler\NodeCompiler;

class ViewCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("jc\\ui\\xhtml\\Node",$aObject,'aObject') ;

		$aAttrs = $aObject->attributes() ;
		
		if( $aAttrs->has('for') )
		{
			$sFor = $aAttrs->expression('for') ;
		}
		else 
		{
			$sFor = "\$theView" ;
		}
		
		$aDev->write("<?php ob_flush() ;\r\n") ;
		$aDev->write("\$theView = \$aVariables->get('theView') ;\r\n") ;
		$aDev->write("foreach({$sFor}->iterator() as \$aChildView){\r\n") ;
		$aDev->write("\t\$theView->outputStream()->write(\$aChildView->outputStream()) ;\r\n") ;
		$aDev->write("}?>") ;
	}
	
	static public function FindVagrantView()
	{
		
	}
}

?>