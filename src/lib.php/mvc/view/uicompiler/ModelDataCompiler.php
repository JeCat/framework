<?php
namespace jc\mvc\view\uicompiler ;

use jc\lang\Exception;
use jc\lang\Assert;
use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\ui\TargetCodeOutputStream;
use jc\ui\xhtml\compiler\NodeCompiler;

class ModelDataCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("jc\\ui\\xhtml\\Node",$aObject,'aObject') ;

		$aAttrs = $aObject->attributes();
		
		if( $aAttrs->has ( 'name' ) )
		{
			$sName = $aAttrs->get ( 'name' ) ;
		}
		
		else 
		{
			$aIterator = $aObject->iterator() ;
			if( !$aFirst = $aIterator->current() )
			{
				throw new Exception("%s 对象却少数据名称",$aObject->tagName()) ;
			}
			
			$sName = '"'.addslashes($aFirst->source()).'"' ;
		}
		
		$aDev->write("if(\$theModel=\$aVariables->get('theModel')){\r\n") ;
		$aDev->write("\techo \$theModel->data({$sName}) ;\r\n") ;
		$aDev->write("}\r\n") ;
	}
}

?>