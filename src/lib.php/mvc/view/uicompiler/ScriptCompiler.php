<?php
namespace jc\mvc\view\uicompiler ;

use jc\lang\Assert;
use jc\ui\xhtml\compiler\node\ScriptCompiler as UiScriptCompiler ;
use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\io\IOutputStream;

class ScriptCompiler extends UiScriptCompiler
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("jc\\ui\\xhtml\\Node",$aObject,'aObject') ;
		
		$aAttrs = $aObject->attributes() ;
		if( $aAttrs->has('src') and !$aAttrs->bool('ignore') )
		{
			$sSrc = $aAttrs->get('src') ;
			$aDev->write("<?php \\jc\\resrc\\HtmlResourcePool::singleton()->addRequire({$sSrc},\\jc\\resrc\\HtmlResourcePool::RESRC_JS) ; ?>") ;
		}
		else 
		{
			parent::compile($aObject, $aDev, $aCompilerManager) ;
		}
	}
}


?>