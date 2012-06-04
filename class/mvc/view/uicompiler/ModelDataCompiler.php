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

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

/**
 * @wiki /MVC模式/视图/模板标签
 * @wiki 速查/模板引擎/标签
 *	==<model:data>==
 *
 *  可单行,model数据获取标签,获取model对应的字段的value
 * {|
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |name
 *  |必须
 *  |expression
 *  |
 *  |属性name为model的数据表字段名称
 *  |}
 *  [example php frameworktest template/test-mvc/testview/ViewNode.html 37 38]
 *  
 *  ==<data>==
 *  
 *  可单行,model数据获取标签,获取model对应的字段的value
 * {|
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |name
 *  |必须
 *  |expression
 *  |
 *  |属性name为model的数据表字段名称
 *  |}
 *  [example php frameworktest template/test-mvc/testview/ViewNode.html 40 41]
 */

class ModelDataCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;

		$aAttrs = $aObject->attributes();
		
		if( $aAttrs->has ( 'name' ) )
		{
			$sName = $aAttrs->get ( 'name' ) ;
		}
		
		else 
		{
			$aIterator = $aObject->iterator() ;
			if( !$aFirst = $aIterator->current() )
			{
				throw new Exception("%s 对象却少数据名称",$aObject->tagName()) ;
			}
			
			$sName = '"'.addslashes($aFirst->source()).'"' ;
		}
		
		$aDev->putCode("if(\$theModel=\$aVariables->get('theModel')){\r\n") ;
		$aDev->putCode("\t\$aDevice->write(\$theModel->data({$sName})) ;\r\n") ;
		$aDev->putCode("}\r\n") ;
	}
}

