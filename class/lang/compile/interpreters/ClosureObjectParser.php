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

namespace org\jecat\framework\lang\compile\interpreters ;

use org\jecat\framework\lang\compile\ClassCompileException;
use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\util\Stack;
use org\jecat\framework\lang\compile\object\ClosureToken;
use org\jecat\framework\lang\compile\object\Token;
use org\jecat\framework\lang\compile\IInterpreter;
use org\jecat\framework\lang\Object;

/**
 * 闭合对象分析
 */
class ClosureObjectParser extends Object implements IInterpreter
{
	public function analyze(TokenPool $aObjectContainer)
	{
		$arrStacks = array(
			Token::T_BRACE_OPEN => new Stack() ,
			Token::T_BRACE_SQUARE_OPEN => new Stack() ,
			Token::T_BRACE_ROUND_OPEN => new Stack() ,
			T_OPEN_TAG => new Stack() ,
		) ;
		$arrStacks[Token::T_BRACE_CLOSE] = $arrStacks[Token::T_BRACE_OPEN] ;
		$arrStacks[Token::T_BRACE_SQUARE_CLOSE] = $arrStacks[Token::T_BRACE_SQUARE_OPEN] ;
		$arrStacks[Token::T_BRACE_ROUND_CLOSE] = $arrStacks[Token::T_BRACE_ROUND_OPEN] ;
		$arrStacks[T_OPEN_TAG_WITH_ECHO] = $arrStacks[T_OPEN_TAG] ;
		$arrStacks[T_CLOSE_TAG] = $arrStacks[T_OPEN_TAG] ;
		$arrStacks[T_DOLLAR_OPEN_CURLY_BRACES] = $arrStacks[Token::T_BRACE_OPEN] ;
		$arrStacks[T_CURLY_OPEN] = $arrStacks[Token::T_BRACE_OPEN] ;
		
		$aTokenIter = $aObjectContainer->iterator() ;
		foreach($aTokenIter as $aObject)
		{
			$nIdx = $aTokenIter->key() ;
			
			$tokenType = $aObject->tokenType() ;
			if( !in_array($tokenType,ClosureToken::openClosureTokens()) and !in_array($tokenType,ClosureToken::closeClosureTokens()) )
			{
				continue ;
			}
			
			$aNewToken = new ClosureToken($aObject) ;
			$aObjectContainer->replace($aObject,$aNewToken) ;
			
			if( $aNewToken->isOpen() )
			{
				$arrStacks[$tokenType]->put($aNewToken) ;
			}
			
			else
			{
				if( !$aOpenToken=$arrStacks[$tokenType]->out() )
				{
					throw new ClassCompileException(
								null, $aNewToken
								, "编译class时遇到了语法错误,闭合对象的结尾没有对应的开始:%s; on position %d"
								, array($aNewToken->sourceCode(),$aNewToken->position())
					) ;
				}
				
				$aNewToken->setTheOther($aOpenToken) ;
			}
		}
		
		return ;
	}
	
}

