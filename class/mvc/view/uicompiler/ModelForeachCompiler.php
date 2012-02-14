<?php
namespace org\jecat\framework\mvc\view\uicompiler ;

use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

class ModelForeachCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;
		if( !$aObjectContainer->variableDeclares()->hasDeclared('aStackForLoopIsEnableToRun') )
		{
			$aObjectContainer->variableDeclares()->declareVarible('aStackForLoopIsEnableToRun','new \\org\\jecat\\framework\\util\\Stack()') ;
		}
		$aAttrs = $aObject->attributes();
		$sIdx = $aAttrs->has ( 'idx' ) ? $aAttrs->string ( 'idx' ) : '' ;
		$sItem = $aAttrs->has ( 'item' ) ? $aAttrs->string ( 'item' ) : 'theModel' ;
		$sFor = $aAttrs->has ( 'for' ) ? $aAttrs->get ( 'for' ) : "\$aVariables->get('theModel')" ;
		
		$aDev->write("if(\$aForModel={$sFor}){\r\n") ;
		
		if($sIdx)
		{
			$aDev->write("\t\${$sIdx}=0;\r\n") ;
		}
		
		$aDev->write("\t\$aStackForLoopIsEnableToRun->put(false);") ;
		
		$aDev->write("\t\tforeach(\$aForModel->childIterator() as \$__aChildModel){\r\n") ;
		$aDev->write("\t\t\t\$aVariables->set('{$sItem}',\$__aChildModel) ;
		\$bLoopIsEnableToRun = & \$aStackForLoopIsEnableToRun->getRef();
		\$bLoopIsEnableToRun = true;\r\n") ;
	
		if($sIdx)
		{
			$aDev->write("\t\t\t\$aVariables->set('{$sIdx}',\${$sIdx}++) ;\r\n") ;
		}
		
		
		if(!$aObject->headTag()->isSingle())
		{
			
			$this->compileChildren($aObject,$aObjectContainer,$aDev,$aCompilerManager) ;

			$aDev->write("\t}\r\n}\r\n") ;
		}
	}
}

?>
