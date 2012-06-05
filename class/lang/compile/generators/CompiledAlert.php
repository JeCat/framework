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
namespace org\jecat\framework\lang\compile\generators ;

use org\jecat\framework\lang\compile\ClassCompileException;
use org\jecat\framework\lang\compile\object\FunctionDefine;
use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\lang\compile\object\Token;
use org\jecat\framework\lang\compile\IGenerator;
use org\jecat\framework\lang\Object;

class CompiledAlert extends Object implements IGenerator
{
	public function generateTargetCode(TokenPool $aTokenPool, Token $aToken)
	{
		if( !($aToken instanceof FunctionDefine) )
		{
			return ;
		}
		
		if( !$aBodyToken=$aToken->bodyToken() )
		{
			return ;
		}
		
		// 函数开始
		$aBodyToken->setTargetCode(
			$aBodyToken->targetCode()
			. "\r\n\t// ---------------------------------------------------------------------------------"
			. "\r\n\t// ALERT: 此文件由 JeCat Class Compiler 自动生成和维护，请不要**直接编辑**此文件！"
			. "\r\n\t//   对此文件的任何改动，都会在下次生成时被新生成的文件覆盖。"
			. "\r\n"
		) ;
		
		// 函数结束
		$aBodyEndToken = $aBodyToken->theOther() ;
		if(!$aBodyEndToken)
		{
			throw new ClassCompileException(null,$aBodyToken,"函数 %s 的函数体没有闭合",$aToken->name()) ;
		}
		$aBodyEndToken->setTargetCode(
			  "\r\n\t// ALERT: 此文件由 JeCat Class Compiler 自动生成和维护，请不要**直接编辑**此文件！"
			. "\r\n\t//   对此文件的任何改动，都会在下次生成时被新生成的文件覆盖。"
			. "\r\n\t// ---------------------------------------------------------------------------------"
			. "\r\n\t".$aBodyEndToken->targetCode()
		) ;
	}
}

