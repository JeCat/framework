<?php
namespace org\jecat\framework\lang\compile\interpreters\oop ;

use org\jecat\framework\lang\compile\ClassCompileException;

use org\jecat\framework\lang\compile\object\UseDeclare;
use org\jecat\framework\pattern\iterate\INonlinearIterator;
use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\lang\compile\object\Token;

class UseDeclareParser implements ISyntaxParser
{
	public function parse(TokenPool $aTokenPool,INonlinearIterator $aTokenPoolIter,State $aState)
	{
		$aOriToken = $aTokenPoolIter->current() ;
		if( !$aOriToken or $aOriToken->tokenType()!=T_USE )
		{
			return ;
		}
		
		$aTokenPoolIter = clone $aTokenPoolIter ;
		$aNewToken = new UseDeclare($aOriToken) ;
		
		$bFoundAs = false ;
		for( 
			$aTokenPoolIter->next();
			$aToken=$aTokenPoolIter->current() and $aToken->tokenType()!=Token::T_SEMICOLON;
			$aTokenPoolIter->next()
		)
		{
			if( $aToken->tokenType()==T_STRING )
			{
				$aNewToken->addNameToken($aToken) ;
			}
			else if( $aToken->tokenType()==T_AS )
			{
				$bFoundAs = true ;
				break ;
			}
		}
		
		// 寻找 as 
		if( $bFoundAs )
		{
			$aAsNameToken = null ;
			for( 
				$aTokenPoolIter->next();
				$aToken=$aTokenPoolIter->current() and $aToken->tokenType()!=Token::T_SEMICOLON;
				$aTokenPoolIter->next()
			)
			{
				if( $aToken->tokenType()==T_STRING )
				{
					$aAsNameToken = $aToken ;
					break ;
				}
			}
			if(!$aAsNameToken)
			{
				throw new ClassCompileException(null,$aNewToken,"编译class时遇到错误，as关键字后没有有效的名称") ;
			}
			$aNewToken->setAsNameToken($aAsNameToken) ;
		}
		
		$aTokenPool->replace($aOriToken,$aNewToken) ;
		$aState->addUseDeclare($aNewToken) ;
	}
}

?>