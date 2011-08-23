<?php
namespace jc\mvc\view\uicompiler ;

use jc\ui\xhtml\compiler\node\ClearCompiler;

use jc\lang\Assert;
use jc\ui\IObject;
use jc\ui\CompilerManager;
use jc\ui\TargetCodeOutputStream;
use jc\ui\xhtml\compiler\NodeCompiler;

class CssCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("jc\\ui\\xhtml\\Node",$aObject,'aObject') ;
		
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
			$aDev->write("\\jc\\resrc\\HtmlResourcePool::singleton()->addRequire({$sHref},\\jc\\resrc\\HtmlResourcePool::RESRC_CSS) ;") ;
			
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