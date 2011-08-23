<?php
namespace jc\mvc\view\uicompiler ;

use jc\lang\Assert;
use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\ui\TargetCodeOutputStream;
use jc\ui\xhtml\compiler\NodeCompiler;

class ModelForeachCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("jc\\ui\\xhtml\\Node",$aObject,'aObject') ;

		$aAttrs = $aObject->attributes();
		$sIdx = $aAttrs->has ( 'idx' ) ? $aAttrs->get ( 'idx' ) : '' ;
		$sFor = $aAttrs->has ( 'for' ) ? $aAttrs->get ( 'for' ) : "\$aVariables->get('theModel')" ;
		
		$aDev->write("if(\$aForModel={$sFor}){\r\n") ;
		
		if($sIdx)
		{
			$aDev->write("\t\${$sIdx}=0;\r\n") ;
		}
		
		$aDev->write("\tforeach(\$aForModel->childIterator() as \$aChildModel){\r\n") ;
		$aDev->write("\t\t\$aVariables->set('theModel',\$aChildModel) ;\r\n") ;
	
		if($sIdx)
		{
			$aDev->write("\t\t\$aVariables->set('{$sIdx}',\${$sIdx}++) ;\r\n") ;
		}
		
		
		if(!$aObject->headTag()->isSingle())
		{
			
			$this->compileChildren($aObject,$aDev,$aCompilerManager) ;

			$aDev->write("\t}\r\n}\r\n") ;
		}
	}
}

?>