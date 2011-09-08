<?php
namespace jc\lang\aop\compiler ;

use jc\lang\compile\object\Token;

use jc\lang\Assert;
use jc\lang\Exception;
use jc\lang\compile\object\CallFunction;

class CallFunctionGenerator extends AOPWeaveGenerator
{
	protected function generateAdviceArgvs(GenerateStat $aStat)
	{
		Assert::type("jc\\lang\\compile\\object\\CallFunction", $aStat->aExecutePoint) ;
	
		$aArgvs = $aStat->aExecutePoint->argvToken() ;
		$aArgvsEnd = $aArgvs->theOther() ;
		$aTokenPoolIter = $aStat->aTokenPool->iterator() ;
		$nPos = $aTokenPoolIter->search( $aArgvs ) ;
		if($nPos===false)
		{
			throw new Exception("函数调用的参数token不在TokenPool 中") ;
		}
		$aTokenPoolIter->seek($nPos) ;
		
		$aStat->sAdviceCallArgvsLit = '' ;
		
		
		for( $aTokenPoolIter->next(); $aTokenPoolIter->valid() and ($aToken=$aTokenPoolIter->current())!=$aArgvsEnd; $aTokenPoolIter->next() )
		{
			$aStat->sAdviceCallArgvsLit.= $aToken->targetCode() ;
		}
		
		$aStat->sAdviceDefineArgvsLit = $aStat->sAdviceCallArgvsLit ;
	}

	protected function replaceOriginExecutePoint(GenerateStat $aStat)
	{
		$aStartToken = $aStat->aExecutePoint->classToken()?: $aStat->aExecutePoint ;
		$aEndToken = $aStat->aExecutePoint->argvToken()->theOther() ;
		
		// 插入调用代码
		$aStat->aTokenPool->insertBefore($aStartToken, new Token(T_STRING,"\$this->".$aStat->aAdvicesDispatchFunc->name()."({$aStat->sAdviceCallArgvsLit})")) ;
		
		// 清除已有代码
		$aTokenPoolIter = $aStat->aTokenPool->iterator() ;
		$aTokenPoolIter->seek( $aTokenPoolIter->search($aStartToken) ) ;
		
		for(;$aTokenPoolIter->valid();$aTokenPoolIter->next()) {
			
			$aToken = $aTokenPoolIter->current() ;
			$aStat->aTokenPool->remove($aToken) ;
			
			if($aToken===$aEndToken)
			{
				break ;
			}
		}
	}
	
	protected function generateOriginJointCode(GenerateStat $aStat)
	{
		Assert::type("jc\\lang\\compile\\object\\CallFunction", $aStat->aExecutePoint) ;

		$aStat->sOriginJointCode = '' ;
		
		// 类名 或 对象变量
		if( $aObject=$aStat->aExecutePoint->classToken() )
		{
			$aStat->sOriginJointCode.= $aObject->targetCode() ;
			
			if(!$aStat->aExecutePoint->classToken())
			{
				throw new Exception("方法调用缺少成员访问符") ;
			}
			
			$aStat->sOriginJointCode.= $aStat->aExecutePoint->setAccessToken() ;
		}
		
		// 函数名
		$aStat->sOriginJointCode.= $aStat->aExecutePoint->targetCode() ;
	}
	
}

?>