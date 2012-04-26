<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.7.1
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
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
			$aDev->preprocessStream()->write("jc\\resrc\\HtmlResourcePool::singleton()->addRequire({$sHref},jc\\resrc\\HtmlResourcePool::RESRC_CSS) ;") ;
			
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

