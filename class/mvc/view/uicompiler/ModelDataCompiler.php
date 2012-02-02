<?php
namespace org\jecat\framework\mvc\view\uicompiler ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

class ModelDataCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;

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
		$aDev->write("\t\$aDevice->write(\$theModel->data({$sName})) ;\r\n") ;
		$aDev->write("}\r\n") ;
	}
}

?>