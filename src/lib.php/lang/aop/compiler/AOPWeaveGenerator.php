<?php
namespace jc\lang\aop\compiler ;

use jc\lang\compile\object\ClosureToken;

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
					$this->weave(new GenerateStat($aTokenPool,$aObject,$aPointcut,$aJointPoint)) ;
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
		
		// advice 参数
		$this->generateAdviceArgvs($aStat) ;
		
		// generate and weave AdviceDispatchFunction
		$this->generateAdviceDispatchFunction($aStat) ;
		
		// 
		$this->generateOriginJointCode($aStat) ;
		
		// weave AdviceDispatchFunction
		$this->weaveAdvices($aStat) ;
		
		// replace origin execut point
		$this->replaceOriginExecutePoint($aStat) ;
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

	abstract protected function replaceOriginExecutePoint(GenerateStat $aStat) ;
	
	/**
	 * 创建并织入一个用于集中调用各个advice的函数
	 */
	protected function generateAdviceDispatchFunction(GenerateStat $aStat)
	{		
		// 执行点所在函数
		if( !$aBelongsFunction=$aStat->aExecutePoint->belongsFunction() )
		{
			throw new Exception("正在切入的连接点不在一个函数中") ;
		}
				
		// 函数体
		$aBodyStart = new ClosureToken(new Token(T_STRING, '{')) ;
		$aBodyEnd = new ClosureToken(new Token(T_STRING, '}')) ;
		$aBodyStart->setTheOther($aBodyEnd) ;
		
		$aStat->aTokenPool->insertAfter($aBelongsFunction->endToken(),$aBodyStart) ;
		$aStat->aTokenPool->insertAfter($aBodyStart,$aBodyEnd) ;
		
		$aStat->aTokenPool->insertBefore($aBodyStart,new Token(T_WHITESPACE, "\r\n\t")) ;
		
		// private
		$aStat->aTokenPool->insertBefore($aBodyStart,new Token(T_PRIVATE, 'private')) ;
		
		// function
		$aStat->aTokenPool->insertBefore($aBodyStart,new Token(T_WHITESPACE, ' ')) ;
		$aStat->aAdvicesDispatchFunc = new FunctionDefine(new Token(T_FUNCTION, 'function')) ;
		$aStat->aTokenPool->insertBefore($aBodyStart,$aStat->aAdvicesDispatchFunc) ;
		$aStat->aAdvicesDispatchFunc->setBodyToken($aBodyStart) ;
		
		// function name
		$aStat->aTokenPool->insertBefore($aBodyStart,new Token(T_WHITESPACE, ' ')) ;
		$sFuncName = 'aop_advice_dispatch_' . md5(spl_object_hash($aStat->aExecutePoint)) ;
		$aFuncNameToken = new Token(T_STRING,$sFuncName) ;
		$aStat->aAdvicesDispatchFunc->setNameToken($aFuncNameToken) ;
		$aStat->aTokenPool->insertBefore($aBodyStart,$aFuncNameToken) ;
		
		// 参数表
		$aArgvLstStart = new ClosureToken(new Token(T_STRING, '(')) ;
		$aArgvLstEnd = new ClosureToken(new Token(T_STRING, ')')) ;
		$aArgvLstStart->setTheOther($aArgvLstEnd) ;
		$aStat->aAdvicesDispatchFunc->setArgListToken($aArgvLstStart) ;
		$aStat->aTokenPool->insertBefore($aBodyStart,$aArgvLstStart) ;
		$aStat->aTokenPool->insertBefore($aBodyStart,new Token(T_STRING,$aStat->sAdviceDefineArgvsLit)) ;
		$aStat->aTokenPool->insertBefore($aBodyStart,$aArgvLstEnd) ;
		
		$aStat->aTokenPool->insertBefore($aBodyStart,new Token(T_WHITESPACE, "\r\n\t")) ;
	}

	private function weaveAdviceDefines(GenerateStat $aStat,$aAdvices)
	{
		$aBodyEnd = $aStat->aAdvicesDispatchFunc->endToken() ;
				
		while($aAdvice=$aAdvices->out())
		{			
			// 织入advice定义代码
			$aStat->aTokenPool->insertAfter($aBodyEnd,$this->generateAdviceDefine($aAdvice,$aStat)) ;
			
			// 织入advice调用代码
			$sAdviceFuncName = $this->generateAdviceWeavedFunctionName($aStat,$aAdvice) ;
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
			$sAdviceFuncName = $this->generateAdviceWeavedFunctionName($aStat,$aFirstAdvice) ;
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
								'self::'. $this->generateAdviceWeavedFunctionName($aStat,$aNextAdvice):
								'$this->'. $this->generateAdviceWeavedFunctionName($aStat,$aNextAdvice))
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

	private function generateAdviceWeavedFunctionName(GenerateStat $aStat,Advice $aAdvice)
	{
		$aToken = $aAdvice->token() ;
		
		return $aToken->name().'_cut_'.$aAdvice->position().'_'.md5(
			spl_object_hash($aStat->aExecutePoint) . '<<' . $aToken->belongsClass()->fullName().'::'.$aToken->name()
		) ;
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
		$sCode.= 'function '. $this->generateAdviceWeavedFunctionName($aStat,$aAdvice) . "({$aStat->sAdviceDefineArgvsLit})\r\n" ;
		
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