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

use org\jecat\framework\lang\Assert;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

/**
 * @wiki /MVC模式/视图/模板标签
 * @wiki 速查/模板引擎/标签
 * ==<widget:msgqueue>==
 * 
 *  可单行,显示widget的所有信息 
 * {|
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |id
 *  |
 *  |expression
 *  |
 *  |区分不同的widget
 *  |}
 *  [example php frameworktest template/test-mvc/testview/ViewNode.html 49 50]
 */
/**
 * @author anubis
 * @example /MVC模式/视图/模板标签
 *
 *
 */

class WidgetMsgQueueCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;
	
		$aAttrs = $aObject->attributes() ;
		
		if( !$aAttrs->has('id') )
		{
			throw new Exception("widget标签缺少必要属性:%s",'id') ;
		}
		
		$sId = $aAttrs->get('id') ;
		
		$aDev->putCode("\$__ui_widget = \$aVariables->get('theView')->widget( {$sId} ) ;\r\n") ;
		$aDev->putCode("if(!\$__ui_widget){") ;
		$aDev->putCode("	throw new \\org\\jecat\\framework\\lang\\Exception('指定的widget id(%s)不存在，无法显示该widget的消息队列',array({$sId})) ; \r\n") ;
		$aDev->putCode("}else{") ;
		
		// 使用 <msgqueue> 节点内部的模板内容
		if( $aTemplate=$aObject->getChildNodeByTagName('template') )
		{
			$sOldMsgQueueVarVarName = '$' . parent::assignVariableName('_aOldMsgQueueVar') ;
		
			$aDev->putCode("	{$sOldMsgQueueVarVarName}=\$aVariables->get('aMsgQueue') ;") ;
			$aDev->putCode("	\$aVariables->set('aMsgQueue',\$__ui_widget->messageQueue()) ;") ;
		
			$this->compileChildren($aTemplate,$aObjectContainer,$aDev,$aCompilerManager) ;
			
			$aDev->putCode("	\$aVariables->set('aMsgQueue',{$sOldMsgQueueVarVarName}) ;") ;
		}
		
		// 使用默认模板
		else 
		{
			$aDev->putCode("	if( \$__ui_widget->messageQueue()->count() ){ \r\n") ;
			$aDev->putCode("		\$__ui_widget->messageQueue()->display(\$this,\$aDevice) ;\r\n") ;
			$aDev->putCode("	}\r\n") ;
		}
		
		$aDev->putCode("}") ;
		
	}
}

