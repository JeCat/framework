<?
namespace org\jecat\framework\lang\compile\generators\translater ;

use org\jecat\framework\lang\compile\ClassCompileException;

use org\jecat\framework\lang\Assert;
use org\jecat\framework\lang\compile\object\Token;
use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\lang\compile\IGenerator;
use org\jecat\framework\lang\Object;

class JavascriptTranslater extends Object implements IGenerator
{
	public function generateTargetCode(TokenPool $aTokenPool, Token $aObject)
	{
		switch( $aObject->tokenType() )
		{
			case T_OBJECT_OPERATOR :				// -> to .
				$aObject->setTargetCode('.') ;
				break ;
				
			case Token::T_CONCAT :					// . to +
				$aObject->setTargetCode('+') ;
				break ;
				
			case T_CONCAT_EQUAL :					// .= to +=
				$aObject->setTargetCode('+=') ;
				break ;
			
			case T_VARIABLE :						// 变量名前的 $
				$aObject->setTargetCode( str_replace('$','',$aObject->sourceCode()) ) ;
				break ;
				
			case T_OPEN_TAG :						// < ?php
				$aObject->setTargetCode( '' ) ;
				break ;
			case T_OPEN_TAG_WITH_ECHO :				// < ?=
				$aObject->setTargetCode( 'echo ' ) ;
				break ;
				
			case T_CLOSE_TAG :						// ? >
				$aObject->setTargetCode( '' ) ;
				break ;
				
			// 字符串压缩到一行
			case T_CONSTANT_ENCAPSED_STRING :
			case T_ENCAPSED_AND_WHITESPACE:
				$sTarget = str_replace("\r","\\r", $aObject->targetCode()) ;
				$sTarget = str_replace("\n","\\n", $sTarget) ;
				$aObject->setTargetCode($sTarget) ;
				break ;
			
			// 转换 foreach (js 中没有foreach)
			case T_ENDFOREACH:
				$this->transForeach($aTokenPool,$aObject) ;
				break ;
		}
	}
	
	private function transForeach(TokenPool $aTokenPool,Token $aObject)
	{
		// 定位迭代器
		$aTokenIter = $aTokenPool->iterator() ;
		if( !$nPos=$aTokenIter->search($aObject) )
		{
			Assert::wrong('提供的 $aObject 不再 $aTokenPool 中') ;
		}
		$aTokenIter->seek($nPos) ;
		
		// 找条件开始的 ”(“
		do{ $aTokenIter->next() ; }
		while( $aToken=$aTokenIter->current() and $aToken->tokenType()!=Token::T_BRACE_ROUND_OPEN ) ;
		if(!$aToken)
		{
			throw new ClassCompileException($aObject, "foreach 后没有  ( ") ;
		}
		
		// 找 ( 到 as 之间的表达值
		$arrExpressions = array() ;
		for( $aTokenIter->next(); $aToken=$aTokenIter->current() and $aToken->tokenType()!=Token::T_AS; $aTokenIter->next() )
		{
			$arrExpressions[] = $aToken ;
		}
	}
	
}