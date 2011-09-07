<?php
namespace jc\lang\aop\compiler ;

use jc\lang\Exception;
use jc\lang\Assert;
use jc\lang\aop\jointpoint\JointPoint;
use jc\lang\aop\Pointcut;
use jc\lang\compile\object\FunctionDefine;
use jc\lang\compile\IGenerator;
use jc\lang\compile\object\TokenPool;
use jc\lang\aop\Advice;
use jc\lang\aop\AOP;
use jc\lang\Object ;
use jc\lang\compile\object\Token;

abstract class AOPWeaveGenerator extends Object implements IGenerator
{
	public function generateTargetCode(TokenPool $aTokenPool, Token $aObject)
	{
		if( !$this->checkTokenType($aObject) )
		{
			throw new Exception("错误的Token类型") ;
		}

		foreach($this->aop()->pointcutIterator() as $aPointcut)
		{
			foreach($aPointcut->jointPoints()->iterator() as $aJointPoint)
			{
				if( $aJointPoint->matchExecutionPoint($aObject) )
				{
					$this->weave($aTokenPool,$aObject,$aPointcut,$aJointPoint) ;
				}
			}
		}
	}
	
	abstract protected function checkTokenType(Token $aObject) ;
	
	abstract protected function weave(TokenPool $aTokenPool, Token $aExecutePoint,Pointcut $aPointcut,JointPoint $aJointPoint) ;
	
	/**
	 * 生成织入代码
	 */
	protected function generateAdviceDefine(Advice $aAdvice,$sArgvLst='')
	{
		$sCode = '' ;
		
		// static
		if( $aAdvice->isStatic() )
		{
			$sCode.= 'static ' ;
		}
		
		// public, protected, private
		$sCode.= $aAdvice->access() . ' ' ;
		
		// function and name
		$sCode.= 'function '. $aAdvice->generateWeavedFunctionName() . "({$sArgvLst})\r\n" ;
		
		// body
		$sCode.= "\t{\r\n" ;
		$sCode.= $aAdvice->source() ;
		$sCode.= "\r\n\t}" ;
		
		return new Token(T_STRING,"\r\n\r\n\t".$sCode) ;
	}
	
	
	
	/**
	 * @return jc\lang\aop\AOP 
	 */
	public function aop()
	{
		if( !$this->aAop )
		{
			$this->aAop = AOP::singleton() ;
		}
		
		return $this->aAop ;
	}
	public function setAop(AOP $aAop)
	{
		$this->aAop = $aAop ;
	}
	
	private $aAop ;
}

?>