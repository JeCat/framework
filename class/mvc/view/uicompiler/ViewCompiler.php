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
//  正在使用的这个版本是：0.8
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
namespace org\jecat\framework\mvc\view\uicompiler ;

use org\jecat\framework\mvc\view\ViewAssembler;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

/**
 * @wiki /MVC模式/视图/模板标签
 * @wiki 速查/模板引擎/标签
 *	==<view/>==
 *
 *  单视图标签,可单行，显示单个视图的所有信息
 * {|
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |name
 *  |可选
 *  |expression
 *  |
 *  |view名称
 *  |---
 *  |container
 *  |可选
 *  |expression
 *  |
 *  |属性container为容器,指明该view所指向的controller
 *  |---
 *  |mode
 *  |可选
 *  |expression
 *  |
 *  |属性mode为显示模式,一共有两中，hard,soft
 *  |}
 *  [example php frameworktest template/test-mvc/testview/ViewNode.html 22 23]
 *  
 *  ==<views/>==
 *  
 *  多视图标签,可单行，显示视图集合的所有信息
 * {|
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |
 *  |
 *  |
 *  |
 *  |
 *  |}
 *  [example php frameworktest template/test-mvc/testview/ViewNode.html 24 25]
 */


class ViewCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;

		$aAttrs = $aObject->attributes() ;
		
		// 指定view名称

		if( $aAttrs->has('xpath') )
		{
			$sXpath = $aAttrs->get('xpath') ;
		}
		else if( $aAttrs->has('name') )
		{
			$sXpath = $aAttrs->get('name') ;
		}
		else
		{
			$sXpath = null ;
		}
		if( $sXpath )
		{
			$arrViewXPaths = "array({$sXpath})" ;
			$bUseXpath = true ;
		}
		else
		{
			$arrViewXPaths = 'null' ;
			$bUseXpath = false ;
		}
		
		$sLayout = $aAttrs->string('layout')?: ViewAssembler::layout_vertical ;
				
		// model
		if($aAttrs->has('mode'))
		{
			$nMode = ViewAssembler::filterModeToPriority($aAttrs->string('mode')) ;
		}
		else
		{
			$nMode = $bUseXpath? ViewAssembler::hard: ViewAssembler::soft ;
		}
		
		$nNodeViewId =& $aDev->properties(true)->getRef('node.view.id') ;
		if( $nNodeViewId===null )
		{
			$nNodeViewId = 0 ;
		}
		else
		{
			$nNodeViewId ++ ;
		}
		
		$aDev->putCode("\r\n// display child views ") ;
		$aDev->putCode("\$aView = \$aVariables->get('theView') ;") ;
		$aDev->putCode("\$aFrame = new jc\\mvc\\view\\View() ;") ;
		$aDev->putCode("foreach( \$aView->assembledIterator() as \$aAssembledView )") ;
		$aDev->putCode("{");
		$aDev->putCode("	\$aFrame->assemble(\$aAssembledView) ;");
		$aDev->putCode("}");
		$aDev->putCode("\$aFrame->show(\$aDevice) ;");
		
		return ;
		
		
		
		
	}
}

