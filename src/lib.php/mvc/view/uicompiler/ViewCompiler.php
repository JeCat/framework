<?php
namespace jc\mvc\view\uicompiler ;

use jc\lang\Assert;
use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\ui\TargetCodeOutputStream;
use jc\ui\xhtml\compiler\NodeCompiler;

class ViewCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("jc\\ui\\xhtml\\Node",$aObject,'aObject') ;

		$aAttrs = $aObject->attributes() ;
		
		$aDev->write("\$theView = \$aVariables->get('theView') ;\r\n") ;
		
		if( $aAttrs->has('for') )
		{
			$sFor = $aAttrs->expression('for') ;
			$sForSrc = addslashes($aAttrs->string('for')) ;
			
			$aDev->write("\$aView = {$sFor} ;\r\n") ;
			$aDev->write("if(\$aView){\r\n") ;
			$aDev->write("\t\$theView->outputStream()->write(\$aView->outputStream()) ;\r\n") ;
			$aDev->write("}else{\r\n") ;
			$aDev->write("\techo '指定的视图不存在：\"{$sForSrc}\"' ;\r\n") ;
			$aDev->write("}\r\n") ;
		}
		else 
		{
			$aDev->write("foreach(\$theView->iterator() as \$aChildView){\r\n") ;
			$aDev->write("\t\$theView->outputStream()->write(\$aChildView->outputStream()) ;\r\n") ;
			$aDev->write("}\r\n") ;
		}
	}
	
	static public function FindVagrantView()
	{
		
	}
}

?>