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
/**
 * while循环
 * <while *exe* >  *loopbody*  </while>
 * 
 * @author anubis
 *
 */
namespace org\jecat\framework\ui\xhtml\compiler\node;

use org\jecat\framework\ui\xhtml\Expression;

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
 * ==<while>==
 * 
 *  不可单行,条件流程控制，匿名属性必须是一个表达式
 * {|
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |@匿名
 *  |必须
 *  |expression
 *  |
 *  |条件表达式
 *  |}
 *  [example php frameworktest template/test-template/node/WhileCase.html 1 12]
 */

class WhileCompiler extends NodeCompiler {
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager) {
		Type::check ( "org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );
		
	
		if( !$aDev->hasDeclared('aStackForLoopIsEnableToRun') )
		{
			$aDev->declareVarible('aStackForLoopIsEnableToRun','new \\org\\jecat\\framework\\util\\Stack()') ;
		}
		
		$sIdxUserName = $aObject->attributes()->has ( 'idx' ) ? $aObject->attributes()->string ( 'idx' ) : '' ;
		$sIdxAutoName = NodeCompiler::assignVariableName ( '$__while_idx_' ) ;
		if( !empty($sIdxUserName) ){
			$aDev->putCode ( "  {$sIdxAutoName} = -1;  \$aStackForLoopIsEnableToRun->put(false); " );
		}
		$aDev->putCode ( " while(" );
		$aDev->putCode ( new Expression ( $aObject->attributes ()->anonymous()->source () ) );
		$aDev->putCode ( "){  \$bLoopIsEnableToRun = & \$aStackForLoopIsEnableToRun->getRef();
			\$bLoopIsEnableToRun = true;" );
		if( !empty($sIdxUserName) ){
			$aDev->putCode ( " {$sIdxAutoName}++; 
							\$aVariables->{$sIdxUserName}={$sIdxAutoName};   ");
		}
		
		if(!$aObject->headTag()->isSingle()){
			$this->compileChildren ( $aObject, $aObjectContainer, $aDev, $aCompilerManager );
			$aDev->putCode ( " }   " );
		}
	}
}

