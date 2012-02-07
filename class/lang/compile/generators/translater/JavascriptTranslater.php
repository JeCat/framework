<?
namespace org\jecat\framework\lang\compile\generators\translater ;

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
			
		}
	}
	
}