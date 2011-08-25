<?php

namespace jc\lang\compile\interpreters ;

use jc\lang\compile\ClassCompileException;
use jc\lang\compile\object\TokenPool;
use jc\util\Stack;
use jc\lang\compile\object\ClosureToken;
use jc\lang\compile\object\Token;
use jc\pattern\composite\IContainer;
use jc\lang\compile\IInterpreter;
use jc\lang\Object;

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
			$aObjectContainer->replace($aNewToken,$aObject) ;
			
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



?>