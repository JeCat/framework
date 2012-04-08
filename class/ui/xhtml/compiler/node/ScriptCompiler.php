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

use org\jecat\framework\ui\xhtml\AttributeValue;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\lang\Type;
use org\jecat\framework\ui\xhtml\compiler\ExpressionCompiler;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

/**
 * @wiki /模板引擎/标签
 * @wiki 速查/模板引擎/标签
 * ==<script>==
 * 
 *  可单行,javascript脚本的使用标签
 * {|
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |type
 *  |可选
 *  |expression
 *  |
 *  |
 *  |---
 *  |src
 *  |可选
 *  |expression
 *  |
 *  |必须要配合<resrc/>这个标签一起使用
 *  |---
 *  |ignore
 *  |可选
 *  |expression
 *  |false
 *  |当ignore为true时，不考虑蜂巢模版的src搜寻url的问题,传统的src的功能恢复
 *  |}
 *  [example php frameworktest template/test-template/node/ScriptCase.html 2 28]
 */

class ScriptCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );
		$aAttrs = $aObject->attributes() ;

		$sType = strtolower($aAttrs->string('type')) ;
		if( in_array($sType, array('text/php','php')) )
		{
			foreach($aObject->iterator() as $aChild)
			{
				if( $aChild instanceof AttributeValue )
				{
					continue ;
				}
				$aDev->write(
					ExpressionCompiler::compileExpression($aChild->source(), $aObjectContainer->variableDeclares(),false,true)
				) ;
			}
		}
		
		// 按照普通 html 节点处理
		else 
		{
			if( $aAttrs->has('src') and !$aAttrs->bool('ignore') )
			{
				$sSrc = $aAttrs->get('src') ;
				$aDev->write("\\org\\jecat\\framework\\resrc\\HtmlResourcePool::singleton()->addRequire({$sSrc},\\org\\jecat\\framework\\resrc\\HtmlResourcePool::RESRC_JS) ;") ;
			
				// 清除后文中的空白字符
				ClearCompiler::clearAfterWhitespace($aObject) ;
			}
			else
			{
				parent::compile($aObject, $aObjectContainer, $aDev, $aCompilerManager) ;
			}
		}
	}

}

