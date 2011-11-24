<?php
namespace jc\mvc\view\uicompiler ;

use jc\lang\Assert;
use jc\lang\Exception;
use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\ui\TargetCodeOutputStream;
use jc\ui\xhtml\compiler\NodeCompiler;

class ViewMsgQueueCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("jc\\ui\\xhtml\\Node",$aObject,'aObject') ;
		
		// 使用 <msgqueue> 节点内部的模板内容
		if( $aTemplate=$aObject->getChildNodeByTagName('template') )
		{
			$sOldMsgQueueVarVarName = '$' . parent::assignVariableName('_aOldMsgQueueVar') ;
		
			$aDev->write("	{$sOldMsgQueueVarVarName}=\$aVariables->get('aMsgQueue') ;") ;
			$aDev->write("	\$aVariables->set('aMsgQueue',\$aVariables->get('theView')->messageQueue()) ;") ;
		
			$this->compileChildren($aTemplate,$aDev,$aCompilerManager) ;
			
			$aDev->write("	\$aVariables->set('aMsgQueue',{$sOldMsgQueueVarVarName}) ;") ;
		}
		
		// 使用默认模板
		else 
		{
			$aDev->write("if( \$aVariables->get('theView')->messageQueue()->count() ){ \r\n") ;
			$aDev->write("	\$aVariables->get('theView')->messageQueue()->display(\$this,\$aDevice) ;\r\n") ;
			$aDev->write("}\r\n") ;
		}
	}
}

?>