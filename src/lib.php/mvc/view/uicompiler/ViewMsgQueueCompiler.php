<?php
namespace jc\mvc\view\uicompiler ;

use jc\lang\Assert;
use jc\lang\Exception;
use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\io\IOutputStream;
use jc\ui\xhtml\compiler\NodeCompiler;

class ViewMsgQueueCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("jc\\ui\\xhtml\\Node",$aObject,'aObject') ;
		
		$aDev->write("<?php if( \$aVariables->get('theView')->messageQueue()->count() ){ \r\n") ;
		$aDev->write("	\$this->display('MsgQueue.template.html',array('aMsgQueue'=>\$aVariables->get('theView')->messageQueue()),\$aDevice) ; ?>\r\n") ;
		$aDev->write("} ?>\r\n") ;
	}
}

?>