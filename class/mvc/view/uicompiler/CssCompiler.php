<?php
namespace org\jecat\framework\mvc\view\uicompiler ;

use org\jecat\framework\ui\xhtml\compiler\node\ClearCompiler;

use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;

class CssCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;
		
		$aAttrs = $aObject->attributes() ;
		
		if( !$aAttrs->has('rel') )
		{
			$aAttrs->set('rel','stylesheet') ;
		}
		if( $aAttrs->has('src') and !$aAttrs->has('href') )
		{
			$aAttrs->set( 'href', $aAttrs->string('src') ) ;
		}
		
		if( strtolower($aAttrs->string('rel'))=='stylesheet' and !$aAttrs->bool('ignore') )
		{
			$sHref = $aAttrs->get('href') ;
			$aDev->write("\\org\\jecat\\framework\\resrc\\HtmlResourcePool::singleton()->addRequire({$sHref},\\org\\jecat\\framework\\resrc\\HtmlResourcePool::RESRC_CSS) ;") ;
			
			// 清除后文中的空白字符
			ClearCompiler::clearAfterWhitespace($aObject) ;
		}
		else 
		{
			$this->compileTag($aObject->headTag(), $aDev, $aCompilerManager) ;
			
			$this->compileChildren($aObject, $aDev, $aCompilerManager) ;
			
			if( $aTailTag=$aObject->tailTag() )
			{
				$this->compileChildren($aObject, $aDev, $aCompilerManager) ;
				$this->compileTag($aTailTag, $aDev, $aCompilerManager) ;
			} 
		}
	}
}


?>