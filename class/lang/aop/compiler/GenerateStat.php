<?php

namespace org\jecat\framework\lang\aop\compiler ;

use org\jecat\framework\lang\aop\jointpoint\JointPoint;
use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\lang\compile\object\Token;
use org\jecat\framework\lang\aop\Pointcut;

class GenerateStat 
{
	public function __construct(TokenPool $aTokenPool,Token $aToken,Pointcut $aPointcut,JointPoint $aJointPoint)
	{
		$this->aTokenPool = $aTokenPool ;
		$this->aExecutePoint = $aToken ;
		$this->aPointcut = $aPointcut ;
		$this->aJointPoint = $aJointPoint ;
	}
	
	/**
	 * @var	org\jecat\framework\lang\compile\object\Token
	 */
	public $aExecutePoint ;
	
	/**
	 * @var	org\jecat\framework\lang\aop\Pointcut
	 */
	public $aPointcut ;
	
	/**
	 * @var	org\jecat\framework\lang\aop\JointPoint
	 */
	public $aJointPoint ;
	
	/**
	 * @var	org\jecat\framework\lang\compile\object\TokenPool
	 */
	public $aTokenPool ;
	
	/**
	 * @var	org\jecat\framework\lang\compile\object\FunctionDefine
	 */
	public $aAdvicesDispatchFunc ;
	
	/**
	 * advice 函数定义 的参数表
	 */
	public $sAdviceDefineArgvsLit = '' ;
	/**
	 * advice 函数调用 的参数表
	 */
	public $sAdviceCallArgvsLit = '' ;
	
	public $sOriginJointCode = '' ;
}

?>