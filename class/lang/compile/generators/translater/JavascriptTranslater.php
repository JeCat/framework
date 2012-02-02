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
			case T_OBJECT_OPERATOR :			// -> to .
				$aObject->setSourceCode('.') ;
				break ;
				
			case Token::T_CONCAT :				// . to +
				$aObject->setSourceCode('+') ;
				break ;
				
			case T_CONCAT_EQUAL :				// .= to +=
				$aObject->setSourceCode('+=') ;
				break ;
		}
	}
}