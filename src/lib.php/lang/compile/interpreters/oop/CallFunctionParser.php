<?php
namespace jc\lang\compile\interpreters\oop ;

use jc\lang\compile\object\CallFunction;
use jc\pattern\iterate\CallbackFilterIterator ;
use jc\pattern\iterate\ReveseIterator ;
use jc\lang\compile\object\NamespaceDeclare;
use jc\pattern\iterate\INonlinearIterator;
use jc\lang\compile\object\TokenPool;
use jc\lang\compile\object\Token;

class CallFunctionParser implements ISyntaxPaser
{
	public function parse(TokenPool $aTokenPool,INonlinearIterator $aTokenPoolIter,State $aState)
	{
		$aToken = $aTokenPoolIter->current() ;
		if( $aToken->tokenType()!==Token::T_BRACE_ROUND_OPEN )
		{
			return ;
		}
		
		// 找参数括号前面的函数名
		// ---------------------------------------------
		$aFindIter = new CallbackFilterIterator(
			new ReveseIterator($aTokenPoolIter)
			, function (INonlinearIterator $aTokenPoolIter){
				switch ( $aTokenPoolIter->current()->tokenType() )
				{
					case T_WHITESPACE :		// 过滤空白token
						return false ;
					case T_STRING :			// 预期的token
						return true ;
					default:				// 遇到意外的token
						$aTokenPoolIter->last() ;
						return true ;
				}			
			}
		) ;
		
		if( !$aCallFunctionName = $aFindIter->next() )
		{
			return ;
		}
		
		// 检查函数名前面的 function 声明
		// ---------------------------------------------
		$aFindIter = new CallbackFilterIterator(
			new ReveseIterator(clone $aTokenPoolIter)
			, function (INonlinearIterator $aTokenPoolIter){
				switch ( $aTokenPoolIter->current()->tokenType() )
				{
					case T_WHITESPACE :		// 过滤空白token
						return false ;
					case T_FUNCTION :		// 预期的token
						return true ;
					default:				// 遇到意外的token
						$aTokenPoolIter->last() ;
						return true ;
				}				
			}
		) ;
		
		// 找到 function 申明，这是函数定义 而不是函数调用
		if( $aFindIter->next() )
		{
			return ;
		}
		
		
		$aCallFunction = new CallFunction($aCallFunctionName,$aToken) ;
		
		// 置换
		$aTokenPool->replace($aCallFunctionName,$aCallFunction) ;
		
		
		// 查找class成员访问符：-> 或 ::
		// ---------------------------------------------
		$aFindIter = new CallbackFilterIterator(
			new ReveseIterator($aTokenPoolIter)
			, function (INonlinearIterator $aTokenPoolIter){
				switch ( $aTokenPoolIter->current()->tokenType() )
				{
					case T_WHITESPACE :				// 过滤空白token
						return false ;
					case T_OBJECT_OPERATOR :		// 预期的token: "->"
						return true ;
					case T_DOUBLE_COLON:
					case T_PAAMAYIM_NEKUDOTAYIM :	// 预期的token: "::"
						return true ;
					default:						// 无效的token
						$aTokenPoolIter->last() ;
						return true ;
				}
			}
		) ;
		
		// 方法
		if( $aAccessSymbol = $aFindIter->next() )
		{
			$aFindIter = new CallbackFilterIterator(
				new ReveseIterator($aTokenPoolIter)
				, function (INonlinearIterator $aTokenPoolIter){
					// 过滤空白token
					return $aTokenPoolIter->current()->tokenType()!==T_WHITESPACE ;
				}
			) ;
			
			$aClass = $aFindIter->next() ;
			
			$aCallFunction->setClassToken($aClass) ;
			$aCallFunction->setAccessToken($aAccessSymbol) ;
		}
			
	}
	
}

?>