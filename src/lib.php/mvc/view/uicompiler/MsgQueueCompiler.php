<?php
namespace jc\mvc\view\uicompiler ;

use jc\lang\Assert;
use jc\lang\Exception;
use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\ui\TargetCodeOutputStream;
use jc\ui\xhtml\compiler\NodeCompiler;

class MsgQueueCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("jc\\ui\\xhtml\\Node",$aObject,'aObject') ;
		
		$aDev->write("<?php \r\n") ;
		
		if( $aObject->attributes()->has('for') )
		{
			$sMsgQueue = $aObject->attributes()->expression('for') ;
		}
		
		else 
		{
			$sMsgQueue = "\$aVariables->get('theView')->messageQueue()" ;
		}
		
		$aDev->write("\$__ui_msgqueue = {$sMsgQueue} ;\r\n") ;		
		$aDev->write("if( \$__ui_msgqueue instanceof \\jc\\message\\IMessageQueueHolder )\r\n") ;	
		$aDev->write("{ \$__ui_msgqueue = \$__ui_msgqueue->messageQueue() ; }\r\n") ;			
		$aDev->write("\\jc\\lang\\Assert::type( '\\\\jc\\\\message\\\\IMessageQueue',\$__ui_msgqueue);\r\n") ;		
		
		$aDev->write("if( \$__ui_msgqueue->count() ){ \r\n") ;
		$aDev->write("	\$__ui_msgqueue->display(\$this,\$aDevice) ;\r\n") ;
		$aDev->write("} ?>\r\n") ;		
	}
}

?>