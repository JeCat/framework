<?php
namespace jc\mvc\view\uicompiler ;

use jc\lang\Assert;
use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\io\IOutputStream;
use jc\ui\xhtml\compiler\NodeCompiler;

class ModelForeachCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("jc\\ui\\xhtml\\Node",$aObject,'aObject') ;

		$aAttrs = $aObject->attributes();
		$sIdx = $aAttrs->has ( 'idx' ) ? $aAttrs->get ( 'idx' ) : '' ;
		$sFor = $aAttrs->has ( 'for' ) ? $aAttrs->get ( 'for' ) : "\$aVariables->get('theModel')" ;
		
		$aDev->write("<?php if(\$aForModel={$sFor}){\r\n") ;
		
		if($sIdx)
		{
			$aDev->write("\${$sIdx}=0;\r\n") ;
		}
		
		$aDev->write("foreach(\$aForModel->childIterator() as \$aChildModel){\r\n") ;
		$aDev->write("\$aVariables->set('theModel',\$aChildModel) ;\r\n") ;
	
		if($sIdx)
		{
			$aDev->write("\$aVariables->set('{$sIdx}',\${$sIdx}++) ;\r\n") ;
		}
		
		$aDev->write("?>\r\n") ;
		
		if(!$aObject->headTag()->isSingle())
		{
			
			$this->compileChildren($aObject,$aDev,$aCompilerManager) ;

			$aDev->write("<?php } } ?>") ;
		}
	}
}

?>