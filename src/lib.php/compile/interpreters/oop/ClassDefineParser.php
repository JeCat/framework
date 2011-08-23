<?php
namespace jc\compile\interpreters\oop ;

use jc\pattern\iterate\INonlinearIterator;
use jc\compile\object\TokenPool;
use jc\compile\object\Token;
use jc\compile\object\ClassDefine;

class ClassDefineParser implements ISyntaxPaser
{
	public function parse(TokenPool $aTokenPool,INonlinearIterator $aTokenPoolIter,State $aState)
	{
		if( !$aOriToken=$aTokenPoolIter->current() )
		{
			return ;
		}
		
		// 已经处于 class 状态中
		if( $aClass=$aState->currentClass() )
		{
			// 遇到
			if( $aOriToken===$aClass->bodyToken()->theOther() )
			{
				$aState->setCurrentClass(null) ;
			}
			
			return ; 
		}
		
		// 
		else 
		{
			if( $aOriToken->tokenType()!=T_CLASS )
			{
				return ;
			}
				
			$aTokenPoolIter = clone $aTokenPoolIter ;
			$aNewToken = new ClassDefine($aOriToken) ;
			
			// class name
			do{ $aTokenPoolIter->next() ; }
			while( $aToken=$aTokenPoolIter->current() and $aToken->tokenType()!=T_STRING ) ;
			
			$aNewToken->setNameToken($aToken) ;
			
			// class body
			do{ $aTokenPoolIter->next() ; }
			while( $aToken=$aTokenPoolIter->current() and $aToken->tokenType()!=Token::T_BRACE_OPEN ) ;
			
			$aNewToken->setBodyToken($aToken) ;		
	
			$aTokenPool->replace($aNewToken, $aOriToken) ;
			$aState->setCurrentClass($aNewToken) ;
			
			$aNewToken->setBelongsNamespace($aState->currentNamespace()) ;
			
			$aTokenPool->addClass($aNewToken) ;
		}
		
	}
}

?>