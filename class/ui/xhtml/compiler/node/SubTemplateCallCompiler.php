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
namespace org\jecat\framework\ui\xhtml\compiler\node;

use org\jecat\framework\ui\xhtml\Expression;

use org\jecat\framework\lang\Exception;
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
 * ==<subtemplate:call>==
 * 
 *  不可单行,调用被定义的模版
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
 *  |---
 *  |vars
 *  |可选
 *  |bool
 *  |false
 *  |是否调用模版中的变量，true为调用，false为不调用
 *  |}
 *  [example php frameworktest template/test-template/node/SubTemplateDefineCase.html 7 10]
 */

class SubTemplateCallCompiler extends NodeCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );

		$aAttributes = $aObject->attributes() ;
	
		if( $aAttributes->has("name") )
		{
			$sSubTemplateName = $aAttributes->string("name") ;		
		}
		else 
		{
			if( !$aSubTemplateNameVal = $aAttributes->anonymous() )
			{
				throw new Exception("subtemplate:define 节点缺少name属性(line:%d)",$aObject->line()) ;
			}
			$sSubTemplateName = $aSubTemplateNameVal->source() ;
		}
		
		if( !is_callable($sSubTemplateName,true) )
		{
			throw new Exception("subtemplate:define 节点的name属性使用了无效的字符：%d",$sSubTemplateName) ;
		}
		
		$sSubTemplateFuncName = '__subtemplate_' . $sSubTemplateName ;
		
		$aDev->putCode("\r\n// -- call subtemplate:{$sSubTemplateName} start---------------------") ;
		
		// 是否继承父模板中的变量
		$bExtendParentVars = $aAttributes->has("vars")? $aAttributes->bool('vars'): false ;
	
		// variables
		if(!$bExtendParentVars)
		{
			$aDev->putCode("\$__subtemplate_aVariables = new \\org\\jecat\\framework\\util\\DataSrc() ;");
			$aDev->putCode("\$__subtemplate_aVariables->addChild(\$aVariables) ;");
		}
		else
		{
			$aDev->putCode("\$__subtemplate_aVariables = \$aVariables ;");
		}
		
		// other variables
		foreach($aAttributes as $sName=>$aValue)
		{
			if( substr($sName,0,4)=='var.' and $sVarName=substr($sName,4) )
			{
				$aDev->putCode('$__subtemplate_aVariables->set("'. addslashes($sVarName) . '",') ;
				$aDev->putCode(new Expression($aValue->source()));
				$aDev->putCode(') ;');
			}
		}
		
		
		
		$aDev->putCode("if( !function_exists('{$sSubTemplateFuncName}') ){") ;
		$aDev->putCode("\t\$aDevice->write(\"正在调用无效的子模板：{$sSubTemplateName}\") ;") ;
		$aDev->putCode("} else {") ;
		$aDev->putCode("\tcall_user_func_array('{$sSubTemplateFuncName}',array(\$__subtemplate_aVariables,\$aDevice)) ;") ;
		$aDev->putCode("}") ;
		
		$aDev->putCode("// -- call subtemplate:{$sSubTemplateName} end ---------------------\r\n\r\n") ;
	}

}

