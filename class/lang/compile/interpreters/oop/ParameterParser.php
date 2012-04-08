<?php
namespace org\jecat\framework\lang\compile\interpreters\oop ;

use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\pattern\iterate\INonlinearIterator;
use org\jecat\framework\lang\compile\object\Token;
use org\jecat\framework\lang\compile\object\Parameter;
use org\jecat\framework\lang\compile\object\ParameterDefaultValue ;

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
