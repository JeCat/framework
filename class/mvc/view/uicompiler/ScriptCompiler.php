<?php
namespace jc\mvc\view\uicompiler ;

use jc\ui\xhtml\compiler\node\ClearCompiler;
use jc\lang\Assert;
use jc\ui\xhtml\compiler\node\ScriptCompiler as UiScriptCompiler ;
use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\ui\TargetCodeOutputStream;

class ScriptCompiler extends UiScriptCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("jc\\ui\\xhtml\\Node",$aObject,'aObject') ;
		
		$aAttrs = $aObject->attributes() ;
		if( $aAttrs->has('src') and !$aAttrs->bool('ignore') )
		{
			$sSrc = $aAttrs->get('src') ;
			$aDev->write("\\jc\\resrc\\HtmlResourcePool::singleton()->addRequire({$sSrc},\\jc\\resrc\\HtmlResourcePool::RESRC_JS) ;") ;
			
			// 清除后文中的空白字符
			ClearCompiler::clearAfterWhitespace($aObject) ;
		}
		else 
		{
			parent::compile($aObject, $aDev, $aCompilerManager) ;
		}
	}
}


?>