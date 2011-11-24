<?php
namespace org\jecat\framework\lang\compile\interpreters\oop ;

use org\jecat\framework\lang\compile\object\NewObject;
use org\jecat\framework\pattern\iterate\CallbackFilterIterator ;
use org\jecat\framework\lang\compile\object\NamespaceDeclare;
use org\jecat\framework\pattern\iterate\INonlinearIterator;
use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\lang\compile\object\Token;

class NewObjectParser implements ISyntaxParser
{
	public function parse(TokenPool $aTokenPool,INonlinearIterator $aTokenPoolIter,State $aState)
	{
		$aTokenPoolIter = clone $aTokenPoolIter ;
		
		$aToken = $aTokenPoolIter->current() ;
		if( $aToken->tokenType()!==T_NEW)
		{
			return ;
		}
		$aNewToken = $aToken;
		
		// 找到new后面的类名
		do{ $aTokenPoolIter->next() ; }
		while( $aToken=$aTokenPoolIter->current() and !( $aToken->tokenType()==T_VARIABLE or $aToken->tokenType()==T_STRING or $aToken->tokenType()==T_NAMESPACE ) );
		
		if( !$aClassName = $aTokenPoolIter->current() )
		{
			return ;
		}
		
		$aNewObject = new NewObject( $aNewToken , $aClassName ) ;
		
		// 置换
		$aTokenPool->replace( $aNewToken , $aNewObject ) ;
		
		// 把属性列表"告诉"NewObject
		do{ $aTokenPoolIter->next() ; }
		while( $aToken=$aTokenPoolIter->current() and $aToken->tokenType()!=Token::T_BRACE_ROUND_OPEN ) ;
		
		$aNewObject->setArgvToken($aToken) ;
	}
}
?>