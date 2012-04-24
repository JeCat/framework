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
namespace org\jecat\framework\mvc\view\uicompiler ;

use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

/**
 * @wiki /MVC模式/视图/模板标签
 * @wiki 速查/模板引擎/标签
 * ==<msgqueue>==
 * 
 *  可单行,显示controller的所有信息,例如错误或者成功的信息.
 *  controller的消息队列包含controller自身,view以及widget的消息队列
 * {|
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |for
 *  |可选
 *  |table
 *  |
 *  |
 *  |---
 *  |template
 *  |可选
 *  |expression
 *  |
 *  |
 *  |---
 *  |mode
 *  |可选
 *  |expression
 *  |soft
 *  |三种状态，hard，soft，force
 *  |}
 *  [example php frameworktest template/test-mvc/testview/ViewNode.html 43 44]
 */
/**
 * @author anubis
 * @example /MVC模式/视图/模板标签
 *
 *
 */

class MsgQueueCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject,'aObject') ;
		
		$aDev->write("\r\n// display message queue -------------------------------------") ;
		
		// 确定需要display的 MessageQueue 对像 
		// ----------------------
		if( $aObject->attributes()->has('for') )
		{
			$aDev->write("\$__ui_msgqueue = ".$aObject->attributes()->expression('for')." ;") ;
		}
		else 
		{
			$aDev->write("\$__ui_msgqueue = \$aVariables->get('theController')? \$aVariables->get('theController')->messageQueue(): null ;") ;
		}
		$aDev->write("if( \$__ui_msgqueue instanceof \\org\\jecat\\framework\\message\\IMessageQueueHolder ){") ;
		$aDev->write("	\$__ui_msgqueue = \$__ui_msgqueue->messageQueue() ;") ;
		$aDev->write("}") ;
		
		$aDev->write("\\org\\jecat\\framework\\lang\\Assert::type( '\\\\org\\jecat\\framework\\\\message\\\\IMessageQueue',\$__ui_msgqueue);") ;
		
		
		// 确定使用的 template 
		// ----------------------
		//  template 属性
		$sTemplate = $aObject->attributes()->has('template')? $aObject->attributes()->get('template'): 'null' ;
		
		// <template> 内部字节点的模板内容
		$sIsSubtemplate = 'false' ;
		if( $aTemplate=$aObject->getChildNodeByTagName('template') )
		{
			$nSubtemplateIndex = (int)$aDev->properties()->get('nMessageQueueSubtemplateIndex') + 1 ;
			$aDev->properties()->set('nMessageQueueSubtemplateIndex',$nSubtemplateIndex) ;
			$sSubTemplateName = '__subtemplate_for_messagequeue_'.$nSubtemplateIndex ;
			
			$aDev->write("if(!function_exists('$sSubTemplateName')){function {$sSubTemplateName}(\$aVariables,\$aDevice){") ;
			$this->compileChildren($aTemplate,$aObjectContainer,$aDev,$aCompilerManager) ;
			$aDev->write("}}") ;
			
			$sTemplate = "'{$sSubTemplateName}'" ;
			$sIsSubtemplate = 'true' ;
		}
		
		// 显示模式
		// -------------------------------
		switch( $aObject->attributes()->has('mode')? strtolower($aObject->attributes()->string('mode')): 'soft' )
		{
			case 'hard' :
				$aDev->write("// display message queue by HARD mode") ;
				$aDev->write("if( !\$__device_for_msgqueue = \$__ui_msgqueue->properties()->get('aDisplayDevice') ){") ;
				$aDev->write("	\$__device_for_msgqueue = new \\org\\jecat\\framework\\io\\OutputStreamBuffer() ;") ;
				$aDev->write("	\$__ui_msgqueue->properties()->set('aDisplayDevice',\$__device_for_msgqueue) ;") ;
				$aDev->write("}") ;
				$aDev->write("\$__device_for_msgqueue->redirect(\$aDevice) ;") ;
				$sCancelDisplay = ' and $__device_for_msgqueue->isEmpty()' ;
				break ;
				
			case 'force' :
				$aDev->write("// display message queue by FORCE mode") ;
				$aDev->write("\$__device_for_msgqueue = \$aDevice ;") ;
				$sCancelDisplay = '' ;
				break ;
				
			default:	// soft
				$aDev->write("// display message queue by SOFT mode") ;
				$aDev->write("if( !\$__device_for_msgqueue = \$__ui_msgqueue->properties()->get('aDisplayDevice') ){") ;
				$aDev->write("	\$__device_for_msgqueue = new \\org\\jecat\\framework\\io\\OutputStreamBuffer() ;") ;
				$aDev->write("	\$__ui_msgqueue->properties()->set('aDisplayDevice',\$__device_for_msgqueue) ;") ;
				$aDev->write("}") ;
				$aDev->write("\$aDevice->write(\$__device_for_msgqueue) ;") ;
				$sCancelDisplay = ' and $__device_for_msgqueue->isEmpty()' ;
				break ;
		}
		
		$aDev->write("if( \$__ui_msgqueue->count(){$sCancelDisplay} ){ ") ;
		$aDev->write("	\$__ui_msgqueue->display(\$this,\$__device_for_msgqueue,{$sTemplate},{$sIsSubtemplate}) ;") ;
		$aDev->write("}") ;
		$aDev->write("// -------------------------------------\r\n") ;
	}
}

