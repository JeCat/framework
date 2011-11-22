<?php

namespace jc\lang\aop\compiler ;

use jc\lang\aop\jointpoint\JointPoint;
use jc\lang\compile\object\TokenPool;
use jc\lang\compile\object\Token;
use jc\lang\aop\Pointcut;

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
	 * @var	jc\lang\compile\object\Token
	 */
	public $aExecutePoint ;
	
	/**
	 * @var	jc\lang\aop\Pointcut
	 */
	public $aPointcut ;
	
	/**
	 * @var	jc\lang\aop\JointPoint
	 */
	public $aJointPoint ;
	
	/**
	 * @var	jc\lang\compile\object\TokenPool
	 */
	public $aTokenPool ;
	
	/**
	 * @var	jc\lang\compile\object\FunctionDefine
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