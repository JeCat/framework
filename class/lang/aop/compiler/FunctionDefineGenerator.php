<?php
namespace org\jecat\framework\lang\aop\compiler ;

use org\jecat\framework\lang\aop\jointpoint\JointPointMethodDefine;
use org\jecat\framework\lang\Type;
use org\jecat\framework\lang\compile\ClassCompileException;
use org\jecat\framework\lang\compile\object\ClassDefine;
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
	public function generateTargetCode(TokenPool $aTokenPool, Token $aObject)
	{
		if( !($aObject instanceof ClassDefine) )
		{
			throw new ClassCompileException(null,$aObject,"传入的类新必须为 ClassDefine: %s",Type::detectType($aObject)) ;
		}
		
		$aClassEnd = $aObject->bodyToken()->theOther() ;
		$sTargetClassName = $aObject->fullName() ;
		
		// 反射父类的所有方法
		$arrParentMethodNames = array() ;
		$aRefParentClass = null ;
		if( $sParentClass = $aObject->parentClassName() )
		{
			if( !class_exists($sParentClass) )	// << 这里可能会触发对父类的编译
			{
				throw new ClassCompileException(null,$aObject,"编译class时遇到错误，class %s 的父类 %s 不存在 ",array($sTargetClassName,$sParentClass)) ;
			}
			$aRefParentClass = new \ReflectionClass($sParentClass) ;
			foreach($aRefParentClass->getMethods() as $aRefParentMethod)
			{
				if( !$aRefParentMethod->isPrivate() and !$aRefParentMethod->isAbstract() and !$aRefParentMethod->isFinal() )  
				{
					$arrParentMethodNames[$aRefParentMethod->getName()] = $aRefParentMethod ;
				}
			}
		}
		
		// 需要编入的方法
		$arrNeedWeaveMethods = array() ;
		foreach($this->aop()->pointcutIterator() as $aPointcut)
		{
			foreach($aPointcut->jointPoints()->iterator() as $aJointPoint)
			{
				if( !($aJointPoint instanceof JointPointMethodDefine) )
				{
					continue ;
				}
				
				// 模糊匹配函数名 -----------------------
				if( $aJointPoint->weaveMethodIsPattern() )
				{
					foreach( $aTokenPool->functionIterator($aObject->fullName()) as $aMethodToken )
					{
						// bingo !
						if( $aJointPoint->matchExecutionPoint($aMethodToken) )
						{
							$sMethodName = $aMethodToken->name() ;
							if( empty($arrNeedWeaveMethods[$sMethodName]) )
							{
								$arrNeedWeaveMethods[$sMethodName] = new GenerateStat($aTokenPool,$aMethodToken) ;
							}
							
							$arrNeedWeaveMethods[$sMethodName]->addAdvices($aPointcut->advices()->iterator()) ;
						}
					}
				}
				
				// 精确匹配函数名 -----------------------
				else
				{
					if($aJointPoint->weaveClass()!=$sTargetClassName)
					{
						continue ;
					}
					
					$sFuncName = $aJointPoint->weaveMethod() ;
					
					if( empty($arrNeedWeaveMethods[$sFuncName]) )
					{
						$arrNeedWeaveMethods[$sFuncName] = new GenerateStat($aTokenPool) ;
					}
					$arrNeedWeaveMethods[$sFuncName]->addAdvices($aPointcut->advices()->iterator()) ;
					
					// 目标类的方法
					if( $aMethodToken=$aTokenPool->findFunction($sFuncName,$sTargetClassName) )
					{
						$arrNeedWeaveMethods[$sFuncName]->aExecutePoint = $aMethodToken ;
					}
					// 目标类的父类的方法
					else if( isset($arrParentMethodNames[$sFuncName]) )
					{
						$aMethodRef = $arrParentMethodNames[$sFuncName] ;
						$aMethodRef instanceof \ReflectionMethod ;
						
						// 产生函数定义和函数调用的参数表
						$this->generateArgvsByReflection($arrNeedWeaveMethods[$sFuncName],$aMethodRef) ;
						
						if( $aMethodRef->isPublic() )
						{
							$sAccess = 'public' ;
						}
						else if( $aMethodRef->isProtected() )
						{
							$sAccess = 'protected' ;
						}
						else if( $aMethodRef->isPrivate() )
						{
							$sAccess = 'private' ;
						}
						
						// 创建一个覆盖父类的方法用于 aop
						$aMethodToken = $this->createMethod($sFuncName,$arrNeedWeaveMethods[$sFuncName]->sAdviceDefineArgvsLit,$sAccess,$aMethodRef->isStatic(),$aTokenPool,$aClassEnd,'insertBefore') ;
						$aMethodToken->setBelongsClass($aObject) ;
						$aMethodToken->setBelongsNamespace($aObject->belongsNamespace()) ;
						$arrNeedWeaveMethods[$sFuncName]->aExecutePoint = $aMethodToken ;

						// 创建函数内容
						$aTokenPool->insertAfter($aMethodToken->bodyToken(),new Token(T_STRING,"
		// 调用父类方法
		return parent::{$sFuncName}({$arrNeedWeaveMethods[$sFuncName]->sAdviceCallArgvsLit}) ;
	")) ;
					}
					// 不存在的方法
					else
					{
						// 创建一个全新的方法用于 aop
						$aMethodToken = $this->createMethod($sFuncName,$arrNeedWeaveMethods[$sFuncName]->sAdviceDefineArgvsLit,'private',false,$aTokenPool,$aClassEnd,'insertBefore') ;
						$aMethodToken->setBelongsClass($aObject) ;
						$aMethodToken->setBelongsNamespace($aObject->belongsNamespace()) ;
						$arrNeedWeaveMethods[$sFuncName]->aExecutePoint = $aMethodToken ;
						
						$aTokenPool->insertAfter($aMethodToken->bodyToken(),new Token(T_STRING," // 这只是一个影子方法 ")) ;
					}
				}
			}
		}
		
		// 开始编织
		foreach($arrNeedWeaveMethods as $aState)
		{
			if( !empty($aState->arrAdvices) )
			{
				$this->weave($aState) ;
			}
		}
	}
	
	protected function generateArgvsByReflection(GenerateStat $aStat,\ReflectionMethod $aMethodRef)
	{
		$aStat->sAdviceCallArgvsLit = array() ;
		$aStat->sAdviceDefineArgvsLit = array() ;
		
		foreach($aMethodRef->getParameters() as $aParamRef)
		{
			$aStat->sAdviceCallArgvsLit[] = '$'.$aParamRef->getName() ;
			
			$sDefineArgv = '' ;
			if($aParamRef->isArray())
			{
				$sDefineArgv.= 'array ' ;
			}
			if($aParamClsRef=$aParamRef->getClass())
			{
				$sDefineArgv.= '\\'.$aParamClsRef->getName().' ' ;
			}
			if($aParamRef->isPassedByReference())
			{
				$sDefineArgv.= '& ' ;
			}
			$sDefineArgv.= '$'.$aParamRef->getName() ;
			
			if($aParamRef->isDefaultValueAvailable())
			{
				$defaultValue = $aParamRef->getDefaultValue() ;
				$sDefineArgv.= '=' . var_export($defaultValue,true) ;
			}
			$aStat->sAdviceDefineArgvsLit[] = $sDefineArgv ;
		}
		
		$aStat->sAdviceCallArgvsLit = implode(',',$aStat->sAdviceCallArgvsLit) ;
		$aStat->sAdviceDefineArgvsLit = implode(',',$aStat->sAdviceDefineArgvsLit) ;
	}
	
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
		$aStat->aExecutePoint->nameToken()->setTargetCode( $aStat->sOriginJointMethodName ) ;
	}

	/**
	 * 创建调用原始链接点的代码
	 */
	protected function generateOriginJointCode(GenerateStat $aStat)
	{
		Assert::type("org\\jecat\\framework\\lang\\compile\\object\\FunctionDefine", $aStat->aExecutePoint) ;
		
		$aStat->sOriginJointMethodName = '__aop_jointpoint_'.$aStat->aExecutePoint->nameToken()->targetCode().'_'.md5($aStat->aExecutePoint->belongsClass()->name()) ;
		
		$aStat->sOriginJointCode = '' ;
		
		$aStat->sOriginJointCode.= $aStat->aExecutePoint->staticToken()? 'self::': '$this->' ;
		$aStat->sOriginJointCode.= $aStat->sOriginJointMethodName ;
	}
	
	/**
	 * 复制一个函数的参数表token
	 */
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
		if(!$aStat->sAdviceDefineArgvsLit)
		{
			$aStat->sAdviceDefineArgvsLit = '' ;
			foreach($this->cloneFunctionArgvLst($aStat->aTokenPool, $aStat->aExecutePoint) as $aToken)
			{
				$aStat->sAdviceDefineArgvsLit.= $aToken->targetCode() ;
			}
		}
		
		// advice 调用参数
		if(!$aStat->sAdviceCallArgvsLit)
		{
			$aStat->sAdviceCallArgvsLit = $this->generateArgvs($aStat->aTokenPool,$aStat->aExecutePoint) ;
		}
	}
}

?>