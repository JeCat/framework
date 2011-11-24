<?php
namespace org\jecat\framework\mvc\view\uicompiler ;

use org\jecat\framework\lang\Assert;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;

class MsgQueueCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;
				
		if( $aObject->attributes()->has('for') )
		{
			$sMsgQueue = $aObject->attributes()->expression('for') ;
		}
		else 
		{
			$sMsgQueue = "\$aVariables->get('theView')->messageQueue()" ;
		}
		$aDev->write("\$__ui_msgqueue = {$sMsgQueue} ;\r\n") ;		
		$aDev->write("if( \$__ui_msgqueue instanceof \\org\\jecat\\framework\\message\\IMessageQueueHolder ){\r\n") ;	
		$aDev->write("\t\$__ui_msgqueue = \$__ui_msgqueue->messageQueue() ;\r\n\t}\r\n") ;			
		$aDev->write("\\org\\jecat\\framework\\lang\\Assert::type( '\\\\org\\jecat\\framework\\\\message\\\\IMessageQueue',\$__ui_msgqueue);\r\n") ;
		
		
		// 使用 <msgqueue> 节点内部的模板内容
		if( $aTemplate=$aObject->getChildNodeByTagName('template') )
		{
			$sOldMsgQueueVarVarName = '$' . parent::assignVariableName('_aOldMsgQueueVar') ;
		
			$aDev->write("{$sOldMsgQueueVarVarName}=\$aVariables->get('aMsgQueue',\$__ui_msgqueue) ;") ;
			$aDev->write("\$aVariables->set('aMsgQueue',\$__ui_msgqueue) ;") ;
		
			$this->compileChildren($aTemplate,$aDev,$aCompilerManager) ;
			
			$aDev->write("\$aVariables->set('aMsgQueue',{$sOldMsgQueueVarVarName}) ;") ;
		}
		
		// 使用默认模板
		else 
		{
			$aDev->write("if( \$__ui_msgqueue->count() ){ \r\n") ;
			$aDev->write("	\$__ui_msgqueue->display(\$this,\$aDevice) ;\r\n") ;
			$aDev->write("}\r\n") ;
		}
	}
}

?>