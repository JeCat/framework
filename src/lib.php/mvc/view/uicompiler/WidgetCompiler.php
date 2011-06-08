<?php
namespace jc\mvc\view\uicompiler ;

use jc\ui\xhtml\compiler\NodeCompiler;
use jc\lang\Exception;
use jc\lang\Assert;
use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\io\IOutputStream;


class WidgetCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("jc\\ui\\xhtml\\Node",$aObject,'aObject') ;

		$aAttrs = $aObject->attributes() ;
		
		if( !$aAttrs->has('id') )
		{
			throw new Exception("widget标签缺少必要属性:%s",'id') ;
		}
		
		$sId = $aAttrs->get('id') ;
		
		$aDev->write("<?php \$_aWidget = \$aVariables->get('theView')->widget({$sId}) ;\r\n") ;
		$aDev->write("if(\$_aWidget){\r\n") ;
		$aDev->write("	\$_aWidget->display(\$this,null,\$aDevice) ;\r\n") ;
		$aDev->write("}else{\r\n") ;
		$aDev->write("	echo '缺少 widget (id:'.{$sId}.')' ;\r\n") ;
		$aDev->write("} ?>\r\n") ;
	}

}

?>