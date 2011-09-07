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
	protected function generateAdviceDispatchFunction(GenerateStat $aStat)
	{
		Assert::type("jc\\lang\\compile\\object\\FunctionDefine", $aStat->aExecutePoint) ;
	
		if( !$aStat->aExecutePoint->bodyToken() )
		{
			throw new Exception("AOP织入遇到错误：正在对一个抽象方法（%s::%s）进行织入操作。"
				,array($aStat->aExecutePoint->belongsClass()->fullName(),$aStat->aExecutePoint->name())) ;
		}
		
		// --------------------------------
		// 新建同名方法
		$aStat->aAdvicesDispatchFunc = $this->buildNewWeavedMethod($aStat->aTokenPool, $aStat->aExecutePoint) ;
		
		// --------------------------------
		// 原始方法改名
		$sWeavedMethodName = $aStat->aExecutePoint->name() ;
		$sNewMethodName = "__aop_jointpoint_".$sWeavedMethodName ;
		$aStat->aExecutePoint->nameToken()->setTargetCode($sNewMethodName) ;
	}
	

	protected function generateOriginJointCode(GenerateStat $aStat)
	{
		Assert::type("jc\\lang\\compile\\object\\FunctionDefine", $aStat->aExecutePoint) ;
		
		$aStat->sOriginJointCode = '' ;
		
		$aStat->sOriginJointCode.= $aStat->aExecutePoint->staticToken()? 'self::': '$this->' ;
		$aStat->sOriginJointCode.= $aStat->aExecutePoint->nameToken()->targetCode() ;
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

	protected function generateAdviceArgvs(GenerateStat $aStat)
	{
		$aStat->sAdviceDefineArgvsLit = '' ;
		foreach($this->cloneFunctionArgvLst($aStat->aTokenPool, $aStat->aAdvicesDispatchFunc) as $aToken)
		{
			$aStat->sAdviceDefineArgvsLit.= $aToken->targetCode() ;
		}
		
		// advice 调用参数
		$aStat->sAdviceCallArgvsLit = $this->generateArgvs($aStat->aTokenPool,$aStat->aAdvicesDispatchFunc) ;
	} 
}

?>