<?php

namespace jc\lang\aop\compiler ;

use jc\lang\compile\object\TokenPool;
use jc\lang\compile\object\Token;
use jc\lang\aop\Pointcut;

class GenerateStat 
{
	public function __construct(Pointcut $aPointcut,Token $aToken,TokenPool $aTokenPool)
	{
		$this->aPointcut = $aPointcut ;
		$this->aExecutePoint = $aToken ;
		$this->aTokenPool = $aTokenPool ;
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
	 * @var	jc\lang\compile\object\TokenPool
	 */
	public $aTokenPool ;
	
	/**
	 * @var	jc\lang\compile\object\FunctionDefine
	 */
	public $aAdvicesDispatchFunc ;
	
	
	public $sAdviceDefineArgvsLit = '' ;
	public $sAdviceCallArgvsLit = '' ;
	
	public $sOriginJointCode = '' ;
}

?>