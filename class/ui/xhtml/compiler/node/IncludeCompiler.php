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
namespace org\jecat\framework\ui\xhtml\compiler\node ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\ui\xhtml\compiler\ExpressionCompiler;
use org\jecat\framework\lang\Type;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

/**
 * @wiki /模板引擎/标签
 * @wiki 速查/模板引擎/标签
 * ==<include>==
 *
 *  可单行,所在位置嵌入模板文件
 * {|
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |--- ---
 *  |@匿名/file
 *  |必须
 *  |string
 *  |
 *  |模板文件名，在模板目录中的相对路径。如果有多个不同namespace的模板目录，可以使用 namespace:templateFilename 的格式
 *  |--- ---
 *  |vars
 *  |可选
 *  |bool
 *  |false
 *  |条件表达式
 *  |}
 *  [example php frameworktest template/test-template/node/IncludeCase.html 1 6]
 */

class IncludeCompiler extends NodeCompiler 
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::check("org\\jecat\\framework\\ui\\xhtml\\Node",$aObject) ;
		$aAttributes = $aObject->attributes() ;

		if( $aAttributes->has("file") )
		{
			$sFileName = '"'.addslashes($aAttributes->string("file")).'"' ;		
		}
		else 
		{
			if( !$aFileVal = $aAttributes->anonymous() )
			{
				throw new Exception("include 节点缺少file属性(line:%d)",$aObject->line()) ;
			}
			$sFileName = '"' . addslashes($aFileVal->source()) . '"' ;
		}
		
		// 是否继承父模板中的变量
		$bExtendParentVars = $aAttributes->has("vars")? $aAttributes->bool('vars'): true ;
		
		// start
		$aDev->write("\r\n");
		
		// variables
		if(!$bExtendParentVars)
		{
			$aDev->write("\$__include_aVariables = new \\org\\jecat\\framework\\util\\DataSrc() ; \r\n");
			$aDev->write("\$__include_aVariables->addChild(\$aVariables) ;");
		}
		else
		{
			$aDev->write("\$__include_aVariables = \$aVariables ; \r\n");
		}
		
		// other variables
		foreach($aAttributes as $sName=>$aValue)
		{
			if( substr($sName,0,4)=='var.' and $sVarName=substr($sName,4) )
			{
				$sVarName = '"'. addslashes($sVarName) . '"' ;
				$sValue = ExpressionCompiler::compileExpression($aValue->source(),$aObjectContainer->variableDeclares()) ;
				$aDev->write("\$__include_aVariables{$sVarName}={$sValue} ; \r\n");
			}
		}
		
		$aDev->write("\$this->display({$sFileName},\$__include_aVariables,\$aDevice) ; ") ;		
	}
}

