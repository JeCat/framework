<?php
namespace jc\lang\aop\compiler ;

use jc\lang\aop\jointpoint\JointPoint;
use jc\lang\aop\Pointcut;
use jc\lang\compile\object\TokenPool;
use jc\lang\compile\object\Token;
use jc\lang\compile\object\CallFunction;

class CallFunctionGenerator extends AOPWeaveGenerator
{
	protected function checkTokenType(Token $aObject)
	{
		return ($aObject instanceof FunctionDefine) ;	
	}
	
	protected function weave(TokenPool $aTokenPool, Token $aFunctionDefine,Pointcut $aPointcut,JointPoint $aJointPoint)
	{
		
	}
}

?>