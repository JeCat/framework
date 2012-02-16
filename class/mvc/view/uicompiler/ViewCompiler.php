<?php
namespace org\jecat\framework\mvc\view\uicompiler ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

class ViewCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;

		$aAttrs = $aObject->attributes() ;
		
		
		$aDev->write("\r\n// display views -------------------") ;
		
		$aDev->write("\$theView = \$aVariables->get('theView') ;") ;
		
		// 指定view名称
		if( $aAttrs->has('name') )
		{
			$sViewXPaths = 'array(' . $aAttrs->get('name') . ')' ;
		}
		else
		{
			$sViewXPaths = 'null' ;
		}
		
		// container
		if( $aAttrs->has('container') )
		{
			$sViewContainer = $aAttrs->get('container') ;
		}
		else
		{
			$sViewContainer = "'controller'" ;
		}
		
		// model
		if($aAttrs->has('mode'))
		{
			$sMode = $aAttrs->get('mode') ;
		}
		else
		{
			// 如果指定名称 hard, 否则 soft
			$sMode = $aAttrs->has('name')? "'hard'": "'soft'" ;
		}
		
		$aDev->write("\$__aViewAssemblySlot = new \\org\\jecat\\framework\\mvc\\view\\ViewAssemblySlot({$sMode},{$sViewXPaths},{$sViewContainer}) ;") ;
		$aDev->write("\$theView->outputStream()->write(\$__aViewAssemblySlot) ;" ) ;
		
		$aDev->write("//-------------------\r\n") ;
	}
}

?>