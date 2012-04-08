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
namespace org\jecat\framework\lang\compile\interpreters\oop ;

use org\jecat\framework\lang\compile\object\DocCommentDeclare;
use org\jecat\framework\pattern\iterate\INonlinearIterator;
use org\jecat\framework\lang\compile\object\FunctionDefine;
use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\lang\compile\object\Token;

class FunctionDefineParser implements ISyntaxParser
{
	public function parse(TokenPool $aTokenPool,INonlinearIterator $aTokenPoolIter,State $aState)
	{
		if( !$aOriToken=$aTokenPoolIter->current() )
		{
			return ;
		}
		
		// 已经处于 function 状态中
		if( $aFunction=$aState->currentFunction() )
		{
			// 遇到
			if( $aFunction->bodyToken() and $aFunction->bodyToken()->theOther()===$aOriToken )
			{
				$aState->setCurrentFunction(null) ;
			}
			
			return ; 
		}
		
		// 
		else 
		{
			if( $aOriToken->tokenType()!=T_FUNCTION )
			{
				return ;
			}
				
			$aTokenPoolIter = clone $aTokenPoolIter ;
			$aNewToken = new FunctionDefine($aOriToken) ;
			
			// function 修饰符 ------
			for(
				$aTokenPoolIter->prev() ;
				$aToken=$aTokenPoolIter->current() ;
				$aTokenPoolIter->prev()
			)
			{
				switch ($aToken->tokenType())
				{
					case T_PUBLIC :
						$aNewToken->setAccessToken($aToken) ;
						break ;
					case T_PROTECTED :
						$aNewToken->setAccessToken($aToken) ;
						break ;
					case T_PRIVATE :
						$aNewToken->setAccessToken($aToken) ;
						break ;
					case T_STATIC :
						$aNewToken->setStaticToken($aToken) ;
						break ;
					case T_ABSTRACT :
						$aNewToken->setAbstractToken($aToken) ;
						break ;
					case T_DOC_COMMENT :
						$aDocToken = new DocCommentDeclare($aToken) ;
						$aNewToken->setDocToken($aDocToken) ;
						$aTokenPool->replace($aToken,$aDocToken) ;
						break (2);
					case T_WHITESPACE :
						break;
					default:
						break(2);
				}
			}
			
			// function name ------
			for($aTokenPoolIter->next();$aToken=$aTokenPoolIter->current();$aTokenPoolIter->next())
			{				
				switch ($aToken->tokenType())
				{
				// 函数名称
				case T_STRING :
					$aNewToken->setNameToken($aToken) ;
					break(2) ;
					
				// 匿名函数
				case Token::T_BRACE_ROUND_OPEN :
					$aTokenPoolIter->prev() ;
					break(2) ;
				case Token::T_BIT_AND :
					$aNewToken->setReturnByRef(true);
					break;
				}
			}
			
			// function argv list ------
			do{ $aTokenPoolIter->next() ; }
			while( $aToken=$aTokenPoolIter->current() and $aToken->tokenType()!=Token::T_BRACE_ROUND_OPEN ) ;
			
			$aNewToken->setArgListToken($aToken) ;
			
			// 移动到参数表结束符号后
			$nPosition = $aTokenPoolIter->search($aToken->theOther()) ;
			$aTokenPoolIter->seek($nPosition) ;
			
			// function body ------
			do{ $aTokenPoolIter->next() ; }
			while( $aToken=$aTokenPoolIter->current() and !in_array($aToken->tokenType(),array(Token::T_BRACE_OPEN,Token::T_SEMICOLON)) ) ;
			
			if( $aToken )
			{
				if( $aToken->tokenType()==Token::T_BRACE_OPEN )
				{
					$aNewToken->setBodyToken($aToken) ;
				
					$aState->setCurrentFunction($aNewToken) ;
				}
				else if( $aToken->tokenType()==Token::T_SEMICOLON )
				{
					$aNewToken->setEndToken($aToken) ;
				}
			}
			
			$aTokenPool->replace($aOriToken,$aNewToken) ;
			
			$aNewToken->setBelongsNamespace($aState->currentNamespace()) ;
			$aNewToken->setBelongsClass($aState->currentClass()) ;
			
			$aTokenPool->addFunction($aNewToken) ;
		}
		
	}
}
