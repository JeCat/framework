<?php
namespace jc\lang\compile\interpreters\oop ;

use jc\pattern\iterate\INonlinearIterator;
use jc\lang\compile\object\TokenPool;
use jc\lang\compile\object\Token;
use jc\lang\compile\object\ClassDefine;

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
			
			// doc comment
			for(
				$aTokenPoolIter->prev() ;
				$aToken=$aTokenPoolIter->current() and in_array($aToken->tokenType(), array(T_ABSTRACT ,T_DOC_COMMENT)) ;
				$aTokenPoolIter->prev()
			)
			{
				switch ($aToken->tokenType())
				{
					case T_ABSTRACT :
						// nothing todo
						break ;
					case T_DOC_COMMENT :
						$aNewToken->setDocToken(
							new DocCommentDeclare($aToken)
						) ;
						break ;
				}
			}
			
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