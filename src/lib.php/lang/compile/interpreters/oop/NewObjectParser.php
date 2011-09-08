<?php
namespace jc\lang\compile\interpreters\oop ;

use jc\lang\compile\object\NewObject;
use jc\pattern\iterate\CallbackFilterIterator ;
use jc\lang\compile\object\NamespaceDeclare;
use jc\pattern\iterate\INonlinearIterator;
use jc\lang\compile\object\TokenPool;
use jc\lang\compile\object\Token;

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
//				// 找到new后面的类名
//		// ---------------------------------------------
//		$aFinderIter = new CallbackFilterIterator(
//			$aTokenPoolIter        //new ReverseIterator($aTokenPoolIter)
//			, function (Iterator $aTokenPoolIter){
//				switch ( $aTokenPoolIter->current()->tokenType() )
//				{
//					case T_WHITESPACE :		// 过滤空白token
//						return false ;
//					case T_STRING :			// 预期的token ,类名
//						return true ;
//					case T_NAMESPACE :		// 预期的token ,完整类名,包括命名空间
//						return true ;
//					case T_VARIABLE :		// 预期的token ,变量形式提供的类名
//						return true ;
//					default:				// 遇到意外的token
//						$aTokenPoolIter->last() ;
//						return false ;
//				}
//			}
//		) ;
		
		// 找到new后面的类名
		do{ $aTokenPoolIter->next() ; }
		while( $aToken=$aTokenPoolIter->current() and !( $aToken->tokenType()==T_VARIABLE or $aToken->tokenType()==T_STRING or $aToken->tokenType()==T_NAMESPACE ) );
		
//		$aTokenPoolIter->next();
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