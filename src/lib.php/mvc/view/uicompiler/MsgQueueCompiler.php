<?php
namespace jc\mvc\view\uicompiler ;

use jc\lang\Assert;
use jc\lang\Exception;
use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\io\IOutputStream;
use jc\ui\xhtml\compiler\NodeCompiler;

class MsgQueueCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
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
		
		$aDev->write( "\$this->display('MsgQueue.template.html',array('aMsgQueue'=>{$sMsgQueue}),\$aDevice) ; ?>\r\n" ) ;
		
	}
}

?>