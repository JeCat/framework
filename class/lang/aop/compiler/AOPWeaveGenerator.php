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

use org\jecat\framework\lang\compile\object\ClosureToken;
use org\jecat\framework\util\Stack;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\compile\object\FunctionDefine;
use org\jecat\framework\lang\compile\IGenerator;
use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\lang\aop\Advice;
use org\jecat\framework\lang\aop\AOP;
use org\jecat\framework\lang\Object;
use org\jecat\framework\lang\compile\object\Token;

abstract class AOPWeaveGenerator extends Object implements IGenerator
{
	public function generateTargetCode(TokenPool $aTokenPool, Token $aObject)
	{
		$arrAdvices = null ;
		
		foreach($this->aop()->pointcutIterator() as $aPointcut)
		{
			$bBingo = false ;
			foreach($aPointcut->jointPoints()->iterator() as $aJointPoint)
			{
				if( $aJointPoint->matchExecutionPoint($aObject) )
				{
					$bBingo = true ;
					break ;
				}
			}
			
			if($bBingo)
			{
				foreach($aPointcut->advices()->iterator() as $aAdvice)
				{
					$arrAdvices[] = $aAdvice ;
				}
			}
		}
		
		if($arrAdvices)
		{
			$this->weave(new GenerateStat($aTokenPool,$aObject,$arrAdvices)) ;
		}
	}

	protected function weave(GenerateStat $aStat)
	{
		$pos = $aStat->aTokenPool->search($aStat->aExecutePoint) ;
		if( $aStat->aExecutePoint and !$aStat->aExecutePoint->belongsClass() )
		{
			throw new Exception("AOP织入遇到错误：正在对一段全局代码进行织入操作，只能对类方法进行织入。") ;
		}
		
		// 为 advice函数 生成 定义和调用的参数
		$this->generateAdviceArgvs($aStat) ;
		
		// generate and weave AdviceDispatchFunction
		$this->generateAdviceDispatchFunction($aStat) ;
		
		// 生成调用原始执行点的代码
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
		foreach($aStat->arrAdvices as $aAdvice)
		{
			$arrAdviceStacks[ $aAdvice->position() ]->put($aAdvice) ;
		}
		
		// 织入执行点上的置换代码：before	
		$this->generateAndWeaveAdviceDefines($aStat,$arrAdviceStacks[Advice::before]) ;
				
		// 织入执行点上的置换代码：around
		$this->generateAndWeaveAroundAdviceDefines($aStat,$arrAdviceStacks[Advice::around]) ;
		
		// 织入执行点上的置换代码：after
		$this->generateAndWeaveAdviceDefines($aStat,$arrAdviceStacks[Advice::after]) ;
		
		
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
		
		$sFuncName = 'aop_advice_dispatch_' . md5(spl_object_hash($aStat->aExecutePoint)) ;
		$this->createMethod($sFuncName,$aStat->sAdviceDefineArgvsLit,'private',false,$aStat->aTokenPool,$aBelongsFunction,'insertAfter') ;
				
		// 函数体
		/*$aBodyStart = new ClosureToken(new Token(T_STRING, '{')) ;
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
		
		$aStat->aTokenPool->insertBefore($aBodyStart,new Token(T_WHITESPACE, "\r\n\t")) ;*/
	}
	
	protected function createMethod($sFuncName,$argLstToken=null,$sAccess,$bStatic=false,TokenPool $aTokenPool,Token $aTargetToken,$sWhere='insertAfter')
	{
		$aFuncToken = new FunctionDefine(new Token(T_FUNCTION, 'function')) ;
		
		// 函数体
		$aBodyStart = new ClosureToken(new Token(T_STRING, '{')) ;
		$aBodyEnd = new ClosureToken(new Token(T_STRING, '}')) ;
		$aBodyStart->setTheOther($aBodyEnd) ;
		
		$aTokenPool->$sWhere($aTargetToken,$aBodyStart) ;
		$aTokenPool->insertAfter($aBodyStart,$aBodyEnd) ;
		$aTokenPool->insertAfter($aBodyEnd,new Token(T_WHITESPACE, "\r\n")) ;
		
		$aTokenPool->insertBefore($aBodyStart,new Token(T_WHITESPACE, "\r\n\t")) ;
		
		// static
		if($bStatic)
		{
			$aStaticToken = new Token(T_PRIVATE, 'static') ;
			$aTokenPool->insertBefore($aBodyStart,$aStaticToken) ;
			$aTokenPool->insertBefore($aBodyStart,new Token(T_WHITESPACE, "\r\n\t")) ;
			$aFuncToken->setStaticToken($aStaticToken) ;
		}
		
		// access
		$arrAccessTokenTypes = array('private'=>T_PRIVATE,'protected'=>T_PROTECTED,'public'=>T_PUBLIC,) ;
		$aAccessToken = new Token($arrAccessTokenTypes[$sAccess], $sAccess) ;
		$aTokenPool->insertBefore($aBodyStart,$aAccessToken) ;
		$aTokenPool->insertBefore($aBodyStart,new Token(T_WHITESPACE, ' ')) ;
		$aFuncToken->setAccessToken($aAccessToken) ;
		
		// function
		$aTokenPool->insertBefore($aBodyStart,$aFuncToken) ;
		$aTokenPool->insertBefore($aBodyStart,new Token(T_WHITESPACE, ' ')) ;
		$aFuncToken->setBodyToken($aBodyStart) ;
		
		// function name
		$aFuncNameToken = new Token(T_STRING,$sFuncName) ;
		$aTokenPool->insertBefore($aBodyStart,$aFuncNameToken) ;
		$aFuncToken->setNameToken($aFuncNameToken) ;
		
		// 参数表
		$aArgvLstStart = new ClosureToken(new Token(T_STRING, '(')) ;
		$aArgvLstEnd = new ClosureToken(new Token(T_STRING, ')')) ;
		$aArgvLstStart->setTheOther($aArgvLstEnd) ;
		$aTokenPool->insertBefore($aBodyStart,$aArgvLstStart) ;
		
		if($argLstToken)
		{
			$aTokenPool->insertBefore($aBodyStart,new Token(T_STRING,$argLstToken)) ;
		}
		
		$aTokenPool->insertBefore($aBodyStart,$aArgvLstEnd) ;
		$aTokenPool->insertBefore($aBodyStart,new Token(T_WHITESPACE, "\r\n\t")) ;
		$aFuncToken->setArgListToken($aArgvLstStart) ;
		
		return $aFuncToken ;
	}
	
	private function generateAndWeaveAdviceDefines(GenerateStat $aStat,$aAdvices)
	{
		if($aAdvices->isEmpty())
		{
			return ;
		}
		
		$aBodyEnd = $aStat->aAdvicesDispatchFunc->endToken() ;
				
		while($aAdvice=$aAdvices->out())
		{			
			// 织入advice定义代码
			if( !$aStat->aTokenPool->insertAfter($aBodyEnd,$this->generateAdviceDefine($aAdvice,$aStat)) )
			{
				throw new Exception("遇到错误！") ;
			}
			
			// 织入advice调用代码
			$sAdviceFuncName = $this->generateAdviceWeavedFunctionName($aStat,$aAdvice) ;
			$sCallType = $this->generateAdviceCalltype($aStat,$aAdvice) ;
			$sAdviceCallCode = "\r\n\t\t{$sCallType}{$sAdviceFuncName}({$aStat->sAdviceCallArgvsLit}) ;\r\n" ;
			$aStat->aTokenPool->insertBefore( $aBodyEnd, new Token(T_STRING,$sAdviceCallCode) ) ;
		}
	}
	
	private function generateAndWeaveAroundAdviceDefines(GenerateStat $aStat,Stack $aAdvices)
	{		
		$aBodyEnd = $aStat->aAdvicesDispatchFunc->endToken() ;
		
		// 织入advice调用代码
		if( $aFirstAdvice=$aAdvices->get() )
		{
			$aStat->aTokenPool->insertBefore($aBodyEnd,new Token(T_STRING,"\r\n\t\t// around advices ----\r\n")) ;
		
			$sAdviceFuncName = $this->generateAdviceWeavedFunctionName($aStat,$aFirstAdvice) ;
			
			// 在 AdviceDispatchFunction 中设置一个 around 类型 advice 的调用代码
			$sCallType = $this->generateAdviceCalltype($aStat,$aFirstAdvice) ;
			$this->weaveAroundAdviceCall($aStat,"{$sCallType}{$sAdviceFuncName}({$aStat->sAdviceCallArgvsLit})") ;
		
			// 陆续植入各个 around 类型 advice
			while($aAdvice=$aAdvices->out())
			{	
				// 生成advice定义代码
				$aAdviceDefine = $this->generateAdviceDefine($aAdvice,$aStat,$aAdvices->get()?:null) ;
				
				// 织入advice定义代码
				$aStat->aTokenPool->insertAfter($aBodyEnd,$aAdviceDefine) ;
			}
		}
		
		// 没有 around advice， 直接调用原始函数
		else 
		{
			$this->weaveAroundAdviceCall($aStat,$aStat->sOriginJointCode."({$aStat->sOriginCallArgvsLit})",true) ;
		}
	}
	
	protected function generateAdviceCalltype(GenerateStat $aStat,Advice $aAdvice)
	{
		return $aAdvice->isStatic()? 'self::': '$this->' ;
	}
	
	protected function weaveAroundAdviceCall(GenerateStat $aStat,$sAdviceCallCode)
	{
		$aBodyEnd = $aStat->aAdvicesDispatchFunc->endToken() ;
		$aStat->aTokenPool->insertBefore($aBodyEnd,new Token(T_STRING,"\t\t{$sAdviceCallCode}) ;\r\n")) ;
	}

	protected function generateAdviceWeavedFunctionName(GenerateStat $aStat,Advice $aAdvice)
	{
		return $aStat->aExecutePoint->belongsFunction()->name().'_cut_'.$aAdvice->position().'_'.md5(
			spl_object_hash($aStat->aExecutePoint) . '<<' . $aAdvice->signtrue()
		) ;
	}
	
	/**
	 * 生成织入代码
	 */
	protected function generateAdviceDefine(Advice $aAdvice,GenerateStat $aStat,$aNextAroundAdvice=null)
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
		$sCode.= $this->compileAdviceCode($aStat,$aAdvice,$aNextAroundAdvice) ;
		$sCode.= "\r\n\t}" ;
		
		return new Token(T_STRING,"\r\n\r\n\t".$sCode) ;
	}
	
	protected function compileAdviceCode(GenerateStat $aStat,Advice $aAdvice,Advice $aNextAroundAdvice=null)
	{		
		return $aAdvice->source() ;
	}
	
	
	/**
	 * @return org\jecat\framework\lang\aop\AOP 
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

