<?php
namespace org\jecat\framework\lang\aop\compiler ;

use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\lang\compile\object\Token;
use org\jecat\framework\lang\compile\object\ClosureToken;
use org\jecat\framework\util\Stack;
use org\jecat\framework\lang\aop\Advice;
use org\jecat\framework\lang\aop\jointpoint\JointPoint;
use org\jecat\framework\lang\aop\Pointcut;
use org\jecat\framework\lang\compile\object\FunctionDefine;
use org\jecat\framework\lang\aop\AOP;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\lang\Exception;

class FunctionDefineGenerator extends AOPWeaveGenerator
{
	/**
	 * 创建并织入一个用于集中调用各个advice的函数
	 */
	protected function generateAdviceDispatchFunction(GenerateStat $aStat)
	{
		Assert::type("org\\jecat\\framework\\lang\\compile\\object\\FunctionDefine", $aStat->aExecutePoint) ;
	
		if( !$aStat->aExecutePoint->bodyToken() )
		{
			throw new Exception("AOP织入遇到错误：正在对一个抽象方法（%s::%s）进行织入操作。"
				,array($aStat->aExecutePoint->belongsClass()->fullName(),$aStat->aExecutePoint->name())) ;
		}
		
		// 新建同名方法
		$aOriFuncStart = $aStat->aExecutePoint->startToken() ;
		$aOriFuncEnd = $aStat->aExecutePoint->endToken() ;
		
		$aStat->aAdvicesDispatchFunc = new FunctionDefine(
				$aStat->aExecutePoint
				, new Token(T_STRING,$aStat->aExecutePoint->name(),0)
				, null, null
		) ;
		
		// static declare
		if($aStat->aExecutePoint->staticToken())
		{
			$aStaticClareToken = new Token(T_STATIC,'static',0) ;
			$aStat->aAdvicesDispatchFunc->setStaticToken($aStaticClareToken) ;
			$aStat->aTokenPool->insertBefore($aOriFuncStart,$aStaticClareToken) ;
			$aStat->aTokenPool->insertBefore($aOriFuncStart,new Token(T_WHITESPACE, ' ', 0)) ;
		}
		
		// private, protected, public
		$aOriAccess = $aStat->aExecutePoint->accessToken() ;
		$aNewAccess = $aOriAccess?
				new Token($aOriAccess->tokenType(),$aOriAccess->sourceCode(),0) :
				new Token(T_PUBLIC,'public',0) ;
		
		$aStat->aAdvicesDispatchFunc->setAccessToken($aNewAccess) ;
		$aStat->aTokenPool->insertBefore($aOriFuncStart,$aNewAccess) ;
		$aStat->aTokenPool->insertBefore($aOriFuncStart,new Token(T_WHITESPACE, ' ', 0)) ;
		
		// function keyword 
		$aStat->aTokenPool->insertBefore($aOriFuncStart,$aStat->aAdvicesDispatchFunc) ;
		$aStat->aTokenPool->insertBefore($aOriFuncStart,new Token(T_WHITESPACE, ' ', 0)) ;
		
		// function name
		$aStat->aTokenPool->insertBefore($aOriFuncStart,$aStat->aAdvicesDispatchFunc->nameToken()) ;
		
		// 参数表
		$aArgvLstStart = new ClosureToken( $aStat->aExecutePoint->argListToken() ) ;
		$aArgvLstEnd = new ClosureToken( $aStat->aExecutePoint->argListToken()->theOther() ) ;
		$aArgvLstStart->setTheOther($aArgvLstEnd) ;
		$aStat->aAdvicesDispatchFunc->setArgListToken($aArgvLstStart) ;
		
		$aStat->aTokenPool->insertBefore($aOriFuncStart,$aArgvLstStart) ;
		foreach($this->cloneFunctionArgvLst($aStat->aTokenPool,$aStat->aExecutePoint) as $aToken)
		{
			$aStat->aTokenPool->insertBefore($aOriFuncStart,$aToken) ;
		}
		$aStat->aTokenPool->insertBefore($aOriFuncStart,$aArgvLstEnd) ;
		
		// 换行
		$aStat->aTokenPool->insertBefore($aOriFuncStart,new Token(T_WHITESPACE,"\r\n\t")) ;
		
		// 函数体
		$aBodyStart = new ClosureToken( $aStat->aExecutePoint->bodyToken() ) ;
		$aBodyEnd = new ClosureToken( $aOriFuncEnd ) ;
		$aBodyStart->setTheOther($aBodyEnd) ;
		$aStat->aAdvicesDispatchFunc->setBodyToken($aBodyStart) ;
			
		$aStat->aTokenPool->insertBefore($aOriFuncStart,$aBodyStart) ;
		$aStat->aTokenPool->insertBefore($aOriFuncStart,$aBodyEnd) ;	
		
		// 换行
		$aStat->aTokenPool->insertBefore($aOriFuncStart,new Token(T_WHITESPACE,"\r\n\r\n\t")) ;
	}
	
	protected function weaveAdvices(GenerateStat $aStat)
	{
		parent::weaveAdvices($aStat) ;
		
		// 添加函数的返回值
		$aStat->aTokenPool->insertBefore($aStat->aAdvicesDispatchFunc->endToken(),new Token(T_WHITESPACE,"\r\n\t\treturn \$__function_return_of_around_advices__ ;\r\n\t")) ;
	}
	
	protected function weaveAroundAdviceCall(GenerateStat $aStat,$sAdviceCallCode)
	{
		$aBodyEnd = $aStat->aAdvicesDispatchFunc->endToken() ;
		$aStat->aTokenPool->insertBefore($aBodyEnd,new Token(T_STRING,"\r\n\r\n\t\t\$__function_return_of_around_advices__ =& {$sAdviceCallCode} ;\r\n")) ;
	}

	protected function replaceOriginExecutePoint(GenerateStat $aStat)
	{
		// 原始方法改名
		$sWeavedMethodName = $aStat->aExecutePoint->name() ;
		$sNewMethodName = "__aop_jointpoint_".$sWeavedMethodName ;
		$aStat->aExecutePoint->nameToken()->setTargetCode($sNewMethodName) ;
	}

	/**
	 * 创建调用原始链接点的代码
	 */
	protected function generateOriginJointCode(GenerateStat $aStat)
	{
		Assert::type("org\\jecat\\framework\\lang\\compile\\object\\FunctionDefine", $aStat->aExecutePoint) ;
		
		$aStat->sOriginJointCode = '' ;
		
		$aStat->sOriginJointCode.= $aStat->aExecutePoint->staticToken()? 'self::': '$this->' ;
		$aStat->sOriginJointCode.= '__aop_jointpoint_'.$aStat->aExecutePoint->nameToken()->targetCode() ;
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
		foreach($this->cloneFunctionArgvLst($aStat->aTokenPool, $aStat->aExecutePoint) as $aToken)
		{
			$aStat->sAdviceDefineArgvsLit.= $aToken->targetCode() ;
		}
		
		// advice 调用参数
		$aStat->sAdviceCallArgvsLit = $this->generateArgvs($aStat->aTokenPool,$aStat->aExecutePoint) ;
	} 
}

?>