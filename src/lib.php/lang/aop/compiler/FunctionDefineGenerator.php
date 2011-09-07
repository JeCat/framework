<?php
namespace jc\lang\aop\compiler ;

use jc\lang\compile\object\TokenPool;
use jc\lang\compile\object\Token;
use jc\lang\compile\object\ClosureToken;
use jc\util\Stack;
use jc\lang\aop\Advice;
use jc\lang\aop\jointpoint\JointPoint;
use jc\lang\aop\Pointcut;
use jc\lang\compile\object\FunctionDefine;
use jc\lang\aop\AOP;
use jc\lang\Assert;
use jc\lang\Exception;

class FunctionDefineGenerator extends AOPWeaveGenerator
{
	public function generateTargetCode(TokenPool $aTokenPool, Token $aObject)
	{
		Assert::type('jc\\lang\\compile\\object\\FunctionDefine', $aObject) ;

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
	
	private function weave(TokenPool $aTokenPool, FunctionDefine $aFunctionDefine,Pointcut $aPointcut,JointPoint $aJointPoint)
	{
		if( !$aFunctionDefine->belongsClass() )
		{
			throw new Exception("AOP织入遇到错误：正在对一个全局函数（%s）进行织入操作，只能对类方法进行织入。",array($aFunctionDefine->name())) ;
		}
		if( !$aFunctionDefine->bodyToken() )
		{
			throw new Exception("AOP织入遇到错误：正在对一个抽象方法（%s::%s）进行织入操作。"
				,array($aFunctionDefine->belongsClass()->fullName(),$aFunctionDefine->name())) ;
		}
		
		// --------------------------------
		// 新建同名方法
		$aNewFunctionDefine = $this->buildNewWeavedMethod($aTokenPool, $aFunctionDefine) ;
		
		
		// --------------------------------
		// 原始方法改名
		$sWeavedMethodName = $aFunctionDefine->name() ;
		$sNewMethodName = "__aop_jointpoint_".$sWeavedMethodName ;
		$aFunctionDefine->nameToken()->setTargetCode($sNewMethodName) ;
		
		
		// --------------------------------
		// 织入advice代码
		if( !$aBodyEnd=$aNewFunctionDefine->endToken() )
		{
			throw new Exception("AOP织入遇到错误：被织入的方法（%s::%s）的函数体定义没有正常结束。"
				,array($aFunctionDefine->belongsClass()->fullName(),$aFunctionDefine->name())) ;
		}
		
		$arrAdviceStacks = array(
			Advice::before => new Stack() ,
			Advice::around => new Stack() ,
			Advice::after => new Stack() ,
		) ;
		
		
		foreach($aPointcut->advices()->iterator() as $aAdvice)
		{
			// 分拣
			$arrAdviceStacks[ $aAdvice->position() ]->put($aAdvice) ;
		}
		
		// advice 定义参数
		$sAdviceDefineArgvLst = '' ;
		foreach($this->cloneFunctionArgvLst($aTokenPool, $aNewFunctionDefine) as $aToken)
		{
			$sAdviceDefineArgvLst.= $aToken->targetCode() ;
		}
		
		// advice 调用参数
		$sArgvLstCode = $this->generateArgvs($aTokenPool,$aNewFunctionDefine) ;
		
		// 织入执行点上的置换代码：before	
		$this->weaveAdvices($arrAdviceStacks[Advice::before],$aTokenPool,$aNewFunctionDefine,$sArgvLstCode,$sAdviceDefineArgvLst) ;
				
		// 织入执行点上的置换代码：around
		$this->weaveAroundAdvices($arrAdviceStacks[Advice::around],$aTokenPool,$aNewFunctionDefine,$aFunctionDefine,$sArgvLstCode,$sAdviceDefineArgvLst) ;
		
		// 织入执行点上的置换代码：after
		$this->weaveAdvices($arrAdviceStacks[Advice::after],$aTokenPool,$aNewFunctionDefine,$sArgvLstCode,$sAdviceDefineArgvLst) ;
		
		
		$aTokenPool->insertBefore($aNewFunctionDefine->endToken(),new Token(T_WHITESPACE,"\r\n\t")) ;
	}

	private function weaveAdvices(Stack $aAdvices,TokenPool $aTokenPool,FunctionDefine $aNewFunctionDefine,$sArgvLstCode,$sAdviceDefineArgvLst)
	{
		$aBodyEnd = $aNewFunctionDefine->bodyToken()->theOther() ;
				
		while($aAdvice=$aAdvices->out())
		{			
			// 织入advice定义代码
			$aTokenPool->insertAfter($aBodyEnd,$this->generateAdviceDefine($aAdvice,$sAdviceDefineArgvLst)) ;
			
			// 织入advice调用代码
			$sAdviceFuncName = $aAdvice->generateWeavedFunctionName() ;
			$aTokenPool->insertBefore( 
					$aNewFunctionDefine->endToken()
					, new Token(T_STRING, "\r\n\t\t".($aAdvice->isStatic()? 'self::': '$this->')."{$sAdviceFuncName}({$sArgvLstCode}) ;\r\n")
			) ;
		}
	}
	
	private function weaveAroundAdvices(Stack $aAdvices,TokenPool $aTokenPool,FunctionDefine $aNewFunctionDefine,FunctionDefine $aOriFunctionDefine,$sArgvLstCode,$sAdviceDefineArgvLst)
	{
		$aBodyEnd = $aNewFunctionDefine->endToken() ;
		
		// 调用原始函数
		$sCallOriginFunction = ($aOriFunctionDefine->staticToken()? 'self::': '$this->') . $aOriFunctionDefine->nameToken()->targetCode() ;

		// 织入advice调用代码
		if( $aFirstAdvice=$aAdvices->get() )
		{
			$sAdviceFuncName = $aFirstAdvice->generateWeavedFunctionName() ;
			$aTokenPool->insertBefore(
				$aBodyEnd
				, new Token(T_STRING, "\r\n\t\t" . ($aFirstAdvice->isStatic()? 'self::': '$this->') . "{$sAdviceFuncName}({$sArgvLstCode}) ;\r\n")
			) ;
		
			while($aAdvice=$aAdvices->out())
			{	
				// 生成advice定义代码
				// -----		
				$aAdviceDefineCode = $this->generateAdviceDefine($aAdvice,$sAdviceDefineArgvLst) ;
				
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
					$aAdviceDefineCode = str_ireplace('aop_call_origin_method',$sCallOriginFunction,$aAdviceDefineCode) ;
				}
				
				// 织入advice定义代码
				// -----		
				$aTokenPool->insertAfter($aBodyEnd,new Token(T_STRING,"\r\n\r\n\t\t".$aAdviceDefineCode)) ;
			}
		
		}
		
		// 没有 around advice， 直接调用原始函数
		else 
		{
			$aTokenPool->insertBefore($aBodyEnd,new Token(T_STRING,"\r\n\r\n\t\t".$sCallOriginFunction."({$sArgvLstCode}) ;\r\n")) ;
		}
	}
	
	private function buildNewWeavedMethod(TokenPool $aTokenPool,FunctionDefine $aOriFunctionDefine)
	{
		$aOriFuncStart = $aOriFunctionDefine->startToken() ;
		$aOriFuncEnd = $aOriFunctionDefine->endToken() ;
	
		$aNewFunctionDefine = new FunctionDefine(
				$aOriFunctionDefine
				, new Token(T_STRING,$aOriFunctionDefine->name(),0)
				, null, null
		) ;
		
		// static declare
		if($aOriFunctionDefine->staticToken())
		{
			$aStaticClareToken = new Token(T_STATIC,'static',0) ;
			$aNewFunctionDefine->setStaticToken($aStaticClareToken) ;
			$aTokenPool->insertBefore($aOriFuncStart,$aStaticClareToken) ;
			$aTokenPool->insertBefore($aOriFuncStart,new Token(T_WHITESPACE, ' ', 0)) ;
		}
		
		// private, protected, public
		$aOriAccess = $aOriFunctionDefine->accessToken() ;
		$aNewAccess = $aOriAccess?
				new Token($aOriAccess->tokenType(),$aOriAccess->source(),0) :
				new Token(T_PUBLIC,'public',0) ;
		$aNewFunctionDefine->setAccessToken($aNewAccess) ;
		$aTokenPool->insertBefore($aOriFuncStart,$aNewAccess) ;
		$aTokenPool->insertBefore($aOriFuncStart,new Token(T_WHITESPACE, ' ', 0)) ;
		
		// function keyword 
		$aTokenPool->insertBefore($aOriFuncStart,$aNewFunctionDefine) ;
		$aTokenPool->insertBefore($aOriFuncStart,new Token(T_WHITESPACE, ' ', 0)) ;
		
		// function name
		$aTokenPool->insertBefore($aOriFuncStart,$aNewFunctionDefine->nameToken()) ;
		
		// 参数表
		$aArgvLstStart = new ClosureToken( $aOriFunctionDefine->argListToken() ) ;
		$aArgvLstEnd = new ClosureToken( $aOriFunctionDefine->argListToken()->theOther() ) ;
		$aArgvLstStart->setTheOther($aArgvLstEnd) ;
		$aNewFunctionDefine->setArgListToken($aArgvLstStart) ;
		
		$aTokenPool->insertBefore($aOriFuncStart,$aArgvLstStart) ;
		foreach($this->cloneFunctionArgvLst($aTokenPool,$aOriFunctionDefine) as $aToken)
		{
			$aTokenPool->insertBefore($aOriFuncStart,$aToken) ;
		}
		$aTokenPool->insertBefore($aOriFuncStart,$aArgvLstEnd) ;
		
		// 换行
		$aTokenPool->insertBefore($aOriFuncStart,new Token(T_WHITESPACE,"\r\n\t")) ;
		
		// 函数体
		$aBodyStart = new ClosureToken( $aOriFunctionDefine->bodyToken() ) ;
		$aBodyEnd = new ClosureToken( $aOriFuncEnd ) ;
		$aBodyStart->setTheOther($aBodyEnd) ;
		$aNewFunctionDefine->setBodyToken($aBodyStart) ;
			
		$aTokenPool->insertBefore($aOriFuncStart,$aBodyStart) ;
		$aTokenPool->insertBefore($aOriFuncStart,$aBodyEnd) ;	
		
		// 换行
		$aTokenPool->insertBefore($aOriFuncStart,new Token(T_WHITESPACE,"\r\n\r\n\t")) ;

		
		return $aNewFunctionDefine ;
	}
	
	private function cloneFunctionArgvLst(TokenPool $aTokenPool,FunctionDefine $aOriFunctionDefine)
	{
		$aArgLstStart = $aOriFunctionDefine->argListToken() ;
		$aArgLstEnd = $aArgLstStart->theOther() ;
		
		$aIter = $aTokenPool->iterator() ;
		$nPos = $aIter->search($aArgLstStart) ;
		if($nPos===false)
		{
			return array() ;
		}
		
		$aIter->seek($nPos) ;
		$aIter->next() ;
		$arrNewTokens = array() ;
		
		while( $aToken=$aIter->current() and $aToken!==$aArgLstEnd )
		{
			$aNewToken = new Token(0,'',0) ;
			$aNewToken->cloneOf($aToken) ;
			
			$arrNewTokens[] = $aNewToken ;
			
			$aIter->next() ;
		} 
		
		return $arrNewTokens ;
	}
	
	private function generateArgvs(TokenPool $aTokenPool,FunctionDefine $aOriFunctionDefine)
	{
		$aArgLstStart = $aOriFunctionDefine->argListToken() ;
		$aArgLstEnd = $aArgLstStart->theOther() ;
		
		$aIter = $aTokenPool->iterator() ;
		$nPos = $aIter->search($aArgLstStart) ;
		if($nPos===false)
		{
			return array() ;
		}
		
		$aIter->seek($nPos) ;
		$aIter->next() ;
		$arrArgvs = array() ;
		
		while( $aToken=$aIter->current() and $aToken!==$aArgLstEnd )
		{
			// 跳过成对的token
			if( ($aToken instanceof ClosureToken) )
			{
				if(!$aTheOtherToken=$aToken->theOther())
				{
					throw new Exception("ClosureToken Token没有正确配对。") ;
				}
				if( ($nPos=$aIter->search($aTheOtherToken))===false )
				{
					throw new Exception("ClosureToken Token配对的token无效。") ;
				}
				$aIter->seek($nPos) ;
			}
					
			if( $aToken->tokenType()===T_VARIABLE )
			{
				$arrArgvs[] = $aToken->targetCode() ;
			}
			
			$aIter->next() ;
		}
		
		return implode(', ', $arrArgvs) ;
	}

}

?>