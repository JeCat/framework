<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.7.1
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\lang\aop\compiler ;

use org\jecat\framework\lang\compile\object\Token;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\lang\Exception;

class CallFunctionGenerator extends AOPWeaveGenerator
{
	protected function generateAdviceArgvs(GenerateStat $aStat)
	{
		Assert::type("org\\jecat\\framework\\lang\\compile\\object\\CallFunction", $aStat->aExecutePoint) ;
	
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
		Assert::type("org\\jecat\\framework\\lang\\compile\\object\\CallFunction", $aStat->aExecutePoint) ;

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
