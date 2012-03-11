<?php

namespace org\jecat\framework\lang\aop\compiler ;

use org\jecat\framework\lang\aop\Advice;

use org\jecat\framework\lang\aop\jointpoint\JointPoint;
use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\lang\compile\object\Token;
use org\jecat\framework\lang\aop\Pointcut;

class GenerateStat 
{
	public function __construct(TokenPool $aTokenPool,Token $aToken=null,array &$arrAdvices=array(),array &$arrAspects=array())
	{
		$this->aTokenPool = $aTokenPool ;
		$this->aExecutePoint = $aToken ;
		$this->arrAdvices =& $arrAdvices ;
		$this->arrAspects =& $arrAspects ;
		
		// 在 TokenPool 中记录 GenerateStat
		$arrAopWeavedStats = $aTokenPool->properties()->get('arrAopWeavedStats')?: array() ;
		$arrAopWeavedStats[] = $this ;
		$aTokenPool->properties()->set('arrAopWeavedStats',$arrAopWeavedStats) ;
	}
	
	public function addAdvice(Advice $aAdvice)
	{
		if( !in_array($aAdvice,$this->arrAdvices,true) )
		{
			$this->arrAdvices[] = $aAdvice ;
		}
	}
	public function addAdvices(\Iterator $aAdviceIter)
	{
		foreach($aAdviceIter as $aAdvice)
		{
			$this->addAdvice($aAdvice) ;
		}
	}
	
	/**
	 * @var	org\jecat\framework\lang\compile\object\Token
	 */
	public $aExecutePoint ;
	
	public $arrAdvices ;
	
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

	public $sOriginJointMethodName = '' ;
	
}

?>