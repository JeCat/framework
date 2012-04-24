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

use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\pattern\iterate\INonlinearIterator;
use org\jecat\framework\lang\compile\object\Token;
use org\jecat\framework\lang\compile\object\Parameter;
use org\jecat\framework\lang\compile\object\ParameterDefaultValue;

class ParameterParser implements ISyntaxParser
{
	public function parse(TokenPool $aTokenPool,INonlinearIterator $aTokenPoolIter,State $aState)
	{
		if( !$aOriToken=$aTokenPoolIter->current() ){
			return ;
		}
		
		// 是否处在function中
		if( !$aFunction=$aState->currentFunction() ){
			return;
		}
		
		// 是否是function的argListToken
		if( $aFunction->argListToken() !== $aOriToken ){
			return ;
		}
		
		$aTokenPoolIter = clone $aTokenPoolIter ;
		$aNewToken = null ;
		
		for($aTokenPoolIter->next();$aToken=$aTokenPoolIter->current();$aTokenPoolIter->next())
		{
			switch ($aToken->tokenType())
			{
			// 结束
			case Token::T_BRACE_ROUND_CLOSE :
				if($aToken === $aOriToken->theOther() ){
					if(null !== $aNewToken){
						$aNewToken->setBelongsFunction($aFunction);
						$aFunction->addParameterToken($aNewToken) ;
						$aTokenPool->replace($aNewToken->nameToken(),$aNewToken) ;
						$aNewToken = null;
					}
					break(2) ;
				}
				break;
			// 类型
			case T_STRING:
				if( null === $aNewToken ){
					$aNewToken = new Parameter($aToken) ;
				}
				$aNewToken->setTypeToken($aToken);
				break;
			// 引用传递
			case Token::T_BIT_AND:
				if( null === $aNewToken ){
					$aNewToken = new Parameter($aToken) ;
				}
				$aNewToken->setReference(true);
				break;
			// 参数名
			case T_VARIABLE:
				if( null === $aNewToken ){
					$aNewToken = new Parameter($aToken) ;
				}
				$aNewToken->setNameToken($aToken);
				break;
			// 默认值
			case Token::T_EQUAL:
				if( null === $aNewToken ){
					$aNewToken = new Parameter($aToken) ;
				}
				$aDefaultValueToken = new ParameterDefaultValue;
				//直接跳到结束（右括号）或下一个参数（逗号）之前
				for($aTokenPoolIter->next();$aEscapeToken=$aTokenPoolIter->current();$aTokenPoolIter->next()){
					switch($aEscapeToken->tokenType()){
					// 在默认值中遇到左括号，直接跳过到它匹配的右括号后面
					case Token::T_BRACE_ROUND_OPEN :
						//$nPosition = $aTokenPoolIter->search($aEscapeToken->theOther()) ;
						//$aTokenPoolIter->seek($nPosition) ;
						// 括号之间的全部加入DefaultValueToken
						$aDefaultValueToken->addSubToken($aEscapeToken);
						do{
							$aTokenPoolIter->next();
							$aInsideBraceToken = $aTokenPoolIter->current();
							$aDefaultValueToken->addSubToken($aInsideBraceToken);
						}while($aInsideBraceToken !== $aEscapeToken->theOther());
						break;
					// 跳到结束右括号之前，不加入DefaultValueToken
					case Token::T_BRACE_ROUND_CLOSE :
						if($aEscapeToken === $aOriToken->theOther() ){
							$aTokenPoolIter->prev();
							break(2) ;
						}
						break;
					// 跳到下一个参数的逗号之前，不加入DefaultValueToken
					case Token::T_COLON:
						$aTokenPoolIter->prev();
						break(2);
					default:
						$aDefaultValueToken->addSubToken($aEscapeToken);
						break;
					}
				}
				$aNewToken->setDefaultValueToken($aDefaultValueToken);
				break;
			// 逗号，下一个参数
			case Token::T_COLON:
				if(null !== $aNewToken){
					$aNewToken->setBelongsFunction($aFunction);
					$aFunction->addParameterToken($aNewToken);
					$aTokenPool->replace($aNewToken->nameToken(),$aNewToken) ;
					$aNewToken = null;
				}
				break;
			}
		}
	}
}


