<?php
namespace org\jecat\framework\ui\xhtml\compiler\node;

use org\jecat\framework\ui\xhtml\compiler\node\ClearCompiler;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

/**
 * @wiki /模板引擎/标签
 * @wiki 速查/模板引擎/标签
 * ==<css/>/<link/>==
 * 
 *  可单行,CSS的定义和引入标签 属性href为引入CSS文件的url
 * {|
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |rel
 *  |可选
 *  |expression
 *  |stylesheet
 *  |
 *  |---
 *  |type
 *  |可选
 *  |expression
 *  |text/css
 *  |
 *  |---
 *  |href
 *  |可选
 *  |expression
 *  |
 *  |当href没有value，但src有value的时候，会将src的value赋给href
 *  |---
 *  |ignore
 *  |可选
 *  |bool
 *  |false
 *  |当ignore为true时,不考虑蜂巢模版的href搜索问题,
 *  |}
 *  [example php frameworktest template/test-template/node/CssCase.html 2 12]
 *  [^]有时候ignore只能在link标签中使用[/^]
 */

class CssCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
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
			$this->compileTag($aObject->headTag(), $aObjectContainer, $aDev, $aCompilerManager) ;
			
			$this->compileChildren($aObject, $aObjectContainer, $aDev, $aCompilerManager) ;
			
			if( $aTailTag=$aObject->tailTag() )
			{
				$this->compileChildren($aObject, $aObjectContainer, $aDev, $aCompilerManager) ;
				$this->compileTag($aTailTag, $aObjectContainer, $aDev, $aCompilerManager) ;
			} 
		}
	}
}


?>