<?php
namespace jc\lang\compile\interpreters\oop ;

use jc\lang\compile\object\NamespaceDeclare;
use jc\pattern\iterate\INonlinearIterator;
use jc\lang\compile\object\TokenPool;
use jc\lang\compile\object\Token;

class NamespaceDeclareParser implements ISyntaxPaser
{
	public function parse(TokenPool $aTokenPool,INonlinearIterator $aTokenPoolIter,State $aState)
	{
		$aOriToken = $aTokenPoolIter->current() ;
		if( !$aOriToken or $aOriToken->tokenType()!=T_NAMESPACE )
		{
			return ;
		}
		
		$aTokenPoolIter = clone $aTokenPoolIter ;
		$aNewToken = new NamespaceDeclare($aOriToken) ;
		
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
		}
		
		$aTokenPool->replace($aOriToken,$aNewToken) ;
		$aState->setCurrentNamespace($aNewToken) ;
	}
}

?>