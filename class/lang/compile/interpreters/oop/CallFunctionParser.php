<?php
namespace org\jecat\framework\lang\compile\interpreters\oop ;

use org\jecat\framework\lang\compile\object\CallFunction;
use org\jecat\framework\pattern\iterate\CallbackFilterIterator ;
use org\jecat\framework\pattern\iterate\ReverseIterator ;
use org\jecat\framework\lang\compile\object\NamespaceDeclare;
use org\jecat\framework\pattern\iterate\IReversableIterator;
use org\jecat\framework\pattern\iterate\INonlinearIterator;
use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\lang\compile\object\Token;

class CallFunctionParser implements ISyntaxParser
{
	public function parse(TokenPool $aTokenPool,INonlinearIterator $aTokenPoolIter,State $aState)
	{
		$aTokenPoolIter = clone $aTokenPoolIter ;
		
		$aToken = $aTokenPoolIter->current() ;
		if( $aToken->tokenType()!==Token::T_BRACE_ROUND_OPEN )
		{
			return ;
		}
		
		// 找参数括号前面的函数名
		// ---------------------------------------------
		$aFinderIter = new CallbackFilterIterator(
			new ReverseIterator($aTokenPoolIter)
			, function (IReversableIterator $aTokenPoolIter){
				switch ( $aTokenPoolIter->current()->tokenType() )
				{
					case T_WHITESPACE :		// 过滤空白token
						return false ;
					case T_STRING :			// 预期的token ,函数名
						return true ;
					default:				// 遇到意外的token
						$aTokenPoolIter->last() ;
						return false ;
				}
			}
		) ;
		
		$aFinderIter->next();
		if( !$aCallFunctionName = $aFinderIter->current() )
		{
			return ;
		}
		
		// 检查函数名前面的 function 声明
		// ---------------------------------------------
		$aFinderIter = new CallbackFilterIterator(
			new ReverseIterator(clone $aTokenPoolIter)
			, function (IReversableIterator $aTokenPoolIter){
				switch ( $aTokenPoolIter->current()->tokenType() )
				{
					case T_WHITESPACE :		// 过滤空白token
						return false ;
					case T_FUNCTION :		// 预期的token
						return true ;
					default:				// 遇到意外的token
						$aTokenPoolIter->last() ;
						return false ;
				}				
			}
		) ;
		
		// 找到 function 申明，这是函数定义 而不是函数调用
		$aFinderIter->next();
		if( $aFinderIter->current() )
		{
			return ;
		}
		
		$aCallFunction = new CallFunction($aCallFunctionName,$aToken) ;
		
		// 置换
		$aTokenPool->replace($aCallFunctionName,$aCallFunction) ;
		
		
		// 查找class成员访问符：-> 或 ::
		// ---------------------------------------------
		$aFinderIter = new CallbackFilterIterator(
			new ReverseIterator($aTokenPoolIter)
			, function (IReversableIterator $aTokenPoolIter){
				switch ( $aTokenPoolIter->current()->tokenType() )
				{
					case T_WHITESPACE :				// 过滤空白token
						return false ;
					case T_OBJECT_OPERATOR :		// 预期的token: "->"
						return true ;
					case T_PAAMAYIM_NEKUDOTAYIM :	// 预期的token: "::"
					case T_DOUBLE_COLON:
						return true ;
					default:						// 无效的token
						$aTokenPoolIter->last() ;
						return false ;
				}
			}
		) ;
		
		// 方法
		$aFinderIter->next();
		if( $aAccessSymbol = $aFinderIter->current() )
		{
			$aFinderIter = new CallbackFilterIterator(
				new ReverseIterator($aTokenPoolIter)
				, function (IReversableIterator $aTokenPoolIter){
					// 过滤空白token
					return $aTokenPoolIter->current()->tokenType()!==T_WHITESPACE ;
				}
			) ;
			
			$aFinderIter->next() ;
			$aClass = $aFinderIter->current() ;
			
			$aCallFunction->setClassToken($aClass) ;
			$aCallFunction->setAccessToken($aAccessSymbol) ;
		}
	}	
}
?>