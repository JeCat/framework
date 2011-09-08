<?php
namespace jc\lang\aop\compiler ;

use jc\util\Stack;

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
		foreach($this->aop()->pointcutIterator() as $aPointcut)
		{
			foreach($aPointcut->jointPoints()->iterator() as $aJointPoint)
			{
				if( $aJointPoint->matchExecutionPoint($aObject) )
				{
					$this->weave(new GenerateStat($aPointcut,$aObject,$aTokenPool)) ;
				}
			}
		}
	}

	protected function weave(GenerateStat $aStat)
	{		
		if( $aStat->aExecutePoint and !$aStat->aExecutePoint->belongsClass() )
		{
			throw new Exception("AOP织入遇到错误：正在对一段全局代码进行织入操作，只能对类方法进行织入。") ;
		}
		
		// generate and weave AdviceDispatchFunction
		$this->generateAdviceDispatchFunction($aStat) ;
		
		// 
		$this->generateOriginJointCode($aStat) ;
		
		// weave AdviceDispatchFunction
		$this->weaveAdvices($aStat) ;
	}

	protected function weaveAdvices(GenerateStat $aStat)
	{
		if( !$aStat->aExecutePoint->belongsClass() )
		{
			throw new Exception("AOP织入遇到错误：正在对一段全局代码进行织入操作，只能对类方法进行织入。") ;
		}
		
		// --------------------------------
		// 织入advice代码
		if( $aStat->aAdvicesDispatchFunc and !$aStat->aAdvicesDispatchFunc->endToken() )
		{
			throw new Exception("AOP织入遇到错误：AdviceDispatchFunction函数体定义没有正常结束。") ;
		}
		
		$arrAdviceStacks = array(
			Advice::before => new Stack() ,
			Advice::around => new Stack() ,
			Advice::after => new Stack() ,
		) ;
		
		// 分拣
		foreach($aStat->aPointcut->advices()->iterator() as $aAdvice)
		{
			$arrAdviceStacks[ $aAdvice->position() ]->put($aAdvice) ;
		}
		
		// advice 参数
		$this->generateAdviceArgvs($aStat) ;
		
		// 织入执行点上的置换代码：before	
		$this->weaveAdviceDefines($aStat,$arrAdviceStacks[Advice::before]) ;
				
		// 织入执行点上的置换代码：around
		$this->weaveAroundAdviceDefines($aStat,$arrAdviceStacks[Advice::around]) ;
		
		// 织入执行点上的置换代码：after
		$this->weaveAdviceDefines($aStat,$arrAdviceStacks[Advice::after]) ;
		
		
		$aStat->aTokenPool->insertBefore($aStat->aAdvicesDispatchFunc->endToken(),new Token(T_WHITESPACE,"\r\n\t")) ;
	}
	
	abstract protected function generateAdviceArgvs(GenerateStat $aStat) ;
	
	abstract protected function generateOriginJointCode(GenerateStat $aStat) ;
	
	/**
	 * 创建并织入一个用于集中调用各个advice的函数
	 */
	protected function generateAdviceDispatchFunction(GenerateStat $aStat)
	{}
		
	private function weaveAdviceDefines(GenerateStat $aStat,$aAdvices)
	{
		$aBodyEnd = $aStat->aAdvicesDispatchFunc->endToken() ;
				
		while($aAdvice=$aAdvices->out())
		{			
			// 织入advice定义代码
			$aStat->aTokenPool->insertAfter($aBodyEnd,$this->generateAdviceDefine($aAdvice,$aStat)) ;
			
			// 织入advice调用代码
			$sAdviceFuncName = $aAdvice->generateWeavedFunctionName() ;
			$sAdviceCallCode = "\r\n\t\t".($aAdvice->isStatic()? 'self::': '$this->')."{$sAdviceFuncName}({$aStat->sAdviceCallArgvsLit}) ;\r\n" ;
			$aStat->aTokenPool->insertBefore( $aBodyEnd, new Token(T_STRING,$sAdviceCallCode) ) ;
		}
	}
	
	private function weaveAroundAdviceDefines(GenerateStat $aStat,$aAdvices)
	{
		$aBodyEnd = $aStat->aAdvicesDispatchFunc->endToken() ;

		// 织入advice调用代码
		if( $aFirstAdvice=$aAdvices->get() )
		{
			$sAdviceFuncName = $aFirstAdvice->generateWeavedFunctionName() ;
			$aStat->aTokenPool->insertBefore(
				$aBodyEnd
				, new Token(T_STRING, "\r\n\t\t" . ($aFirstAdvice->isStatic()? 'self::': '$this->') . "{$sAdviceFuncName}({$aStat->sAdviceCallArgvsLit}) ;\r\n")
			) ;
		
			while($aAdvice=$aAdvices->out())
			{	
				// 生成advice定义代码
				// -----		
				$aAdviceDefineCode = $this->generateAdviceDefine($aAdvice,$aStat) ;
				
				// 调用下一个advice
				if( $aNextAdvice=$aAdvices->get() )
				{
					$aAdviceDefineCode = str_ireplace(
						'aop_call_origin_method'
						, ($aNextAdvice->isStatic()?
								'self::'. $aNextAdvice->generateWeavedFunctionName():
								'$this->'. $aNextAdvice->generateWeavedFunctionName())
						, $aAdviceDefineCode) ;
				}
				
				// 调用原始函数
				else 
				{
					$aAdviceDefineCode = str_ireplace('aop_call_origin_method',$aStat->sOriginJointCode,$aAdviceDefineCode) ;
				}
				
				// 织入advice定义代码
				// -----		
				$aStat->aTokenPool->insertAfter($aBodyEnd,new Token(T_STRING,"\r\n\r\n\t\t".$aAdviceDefineCode)) ;
			}
		
		}
		
		// 没有 around advice， 直接调用原始函数
		else 
		{
			$aStat->aTokenPool->insertBefore($aBodyEnd,new Token(T_STRING,"\r\n\r\n\t\t".$aStat->sOriginJointCode."({$aStat->sAdviceCallArgvsLit}) ;\r\n")) ;
		}
	}
	
	/**
	 * 生成织入代码
	 */
	protected function generateAdviceDefine(Advice $aAdvice,GenerateStat $aStat)
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
		$sCode.= 'function '. $aAdvice->generateWeavedFunctionName() . "({$aStat->sAdviceDefineArgvsLit})\r\n" ;
		
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