<?php
namespace jc\mvc\view\uicompiler ;

use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\io\IOutputStream;
use jc\ui\xhtml\compiler\NodeCompiler;

class LoadResourceCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$aDev->write("<?php \r\n") ;
		$aDev->write("\$__aHtmlResrcPool = \\jc\\mvc\\view\\htmlresrc\\HtmlResourcePoolFactory::singleton()->create() ;\r\n") ;
		$aDev->write("\$aVariables->get('theView')->requireResources(\$__aHtmlResrcPool) ;\r\n") ;
		$aDev->write("foreach(\$__aHtmlResrcPool->iterator(\\jc\\mvc\\view\\htmlresrc\\HtmlResourcePool::RESRC_JS) as \$sJsUrl) {\r\n") ;
		$aDev->write("	echo \"<script type=\\\"text/javascript\\\" src=\\\"{\$sJsUrl}\\\"></script>\\r\\n\" ;") ;
		$aDev->write("}\r\n") ;
		$aDev->write("// output css\r\n") ;
		$aDev->write("foreach(\$__aHtmlResrcPool->iterator(\\jc\\mvc\\view\\htmlresrc\\HtmlResourcePool::RESRC_CSS) as \$sCssUrl) {\r\n") ;
		$aDev->write("	echo \"<link rel=\\\"stylesheet\\\" type=\\\"text/css\\\" href=\\\"{\$sCssUrl}\\\" />\\r\\n\" ;") ;
		$aDev->write("}\r\n") ;
		$aDev->write("?>") ;
	}
}

?>