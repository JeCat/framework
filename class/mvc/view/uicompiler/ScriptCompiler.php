<?php
namespace org\jecat\framework\mvc\view\uicompiler ;

use org\jecat\framework\ui\xhtml\compiler\node\ClearCompiler;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\xhtml\compiler\node\ScriptCompiler as UiScriptCompiler ;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\TargetCodeOutputStream;

class ScriptCompiler extends UiScriptCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;
		
		$aAttrs = $aObject->attributes() ;
		if( $aAttrs->has('src') and !$aAttrs->bool('ignore') )
		{
			$sSrc = $aAttrs->get('src') ;
			$aDev->write("\\org\\jecat\\framework\\resrc\\HtmlResourcePool::singleton()->addRequire({$sSrc},\\org\\jecat\\framework\\resrc\\HtmlResourcePool::RESRC_JS) ;") ;
			
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