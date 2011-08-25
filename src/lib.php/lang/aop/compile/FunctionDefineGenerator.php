<?php
namespace jc\lang\aop\compile ;

use jc\lang\Assert;
use jc\lang\Exception;
use jc\compile\object\TokenPool;
use jc\compile\object\Token ;
use jc\compile\IGenerator ;
use jc\lang\Object ;

class FunctionDefineGenerator extends Object implements IGenerator
{
	public function generateTargetCode(TokenPool $aTokenPool, Token $aObject)
	{
		Assert::type('jc\compile\object\FunctionDefine', $aObject) ;
		
		$aObject->name() ;
		
	}
}

?>