<?php
namespace jc\compile\interpreters\oop ;

use jc\compile\object\NamespaceDeclare;
use jc\pattern\iterate\INonlinearIterator;
use jc\compile\object\TokenPool;
use jc\compile\object\Token;

class NamespaceParser implements ISyntaxPaser
{
	public function parse(TokenPool $aTokenPool,INonlinearIterator $aTokenPoolIter,State $aState)
	{
		$aOriToken = $aTokenPoolIter->current() ;
		$aTokenPoolIter = clone $aTokenPoolIter ;
		
		if( !$aOriToken or $aOriToken->tokenType()!=T_NAMESPACE )
		{
			return ;
		}
		
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
		
		$aTokenPool->replace($aNewToken, $aOriToken) ;
		$aState->setCurrentNamespace($aNewToken) ;
	}
}

?>