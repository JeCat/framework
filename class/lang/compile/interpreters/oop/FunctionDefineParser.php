<?php
namespace org\jecat\framework\lang\compile\interpreters\oop ;

use org\jecat\framework\lang\compile\object\DocCommentDeclare;
use org\jecat\framework\lang\compile\ClassCompileException;
use org\jecat\framework\pattern\iterate\INonlinearIterator;
use org\jecat\framework\lang\compile\object\FunctionDefine;
use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\lang\compile\object\Token;
use org\jecat\framework\lang\compile\object\ClassDefine;

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
#				$aToken=$aTokenPoolIter->current() and in_array($aToken->tokenType(), array(
#						T_PUBLIC ,
#						T_PROTECTED ,
#						T_PRIVATE ,
#						T_STATIC ,
#						T_ABSTRACT ,
#						T_DOC_COMMENT ,
#						T_WHITESPACE ,
#				)) ;
				$aToken=$aTokenPoolIter->current() ;
				$aTokenPoolIter->prev()
			)
			{
				//echo $aToken->tokenType().$aToken."<br/>\n";
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
					case T_COMMENT :
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


?>
