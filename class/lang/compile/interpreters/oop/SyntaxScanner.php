<?php

namespace org\jecat\framework\lang\compile\interpreters\oop ;

use org\jecat\framework\lang\Type;

use org\jecat\framework\pattern\iterate\IReversableIterator;

use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\pattern\iterate\INonlinearIterator;
use org\jecat\framework\lang\compile\object\NamespaceDeclare;
use org\jecat\framework\lang\compile\object\ClassDefine;
use org\jecat\framework\lang\compile\object\Token;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\compile\object\FunctionDefine;
use org\jecat\framework\pattern\composite\IContainer;
use org\jecat\framework\lang\compile\IInterpreter;
use org\jecat\framework\lang\Object;

class SyntaxScanner extends Object implements IInterpreter
{
	public function __construct()
	{
		$this->aPHPCodeParser = new PHPCodeParser() ;
		
		$this->arrParsers[] = new NamespaceDeclareParser() ;
		$this->arrParsers[] = new UseDeclareParser() ;
		$this->arrParsers[] = new ClassDefineParser() ;
		$this->arrParsers[] = new FunctionDefineParser() ;
		$this->arrParsers[] = new CallFunctionParser() ;
		$this->arrParsers[] = new ParameterParser() ;
		//$this->arrParsers[] = new FunctionCallParser() ;
		//$this->arrParsers[] = new NewObjectParser() ;
		//$this->arrParsers[] = new ThrowParser() ;
	}
	
	public function analyze(TokenPool $aTokenPool)
	{
		$aState = new State() ;
		$aTokenPoolIter = $aTokenPool->iterator() ;
		
		foreach($aTokenPoolIter as $aToken)
		{
			// 扫描php代码
			$this->aPHPCodeParser->parse($aTokenPool,$aTokenPoolIter,$aState) ;
			if( !$aState->isPHPCode() )
			{
				continue ;
			}
				
			foreach($this->arrParsers as $aParser)
			{				
				$aParser->parse($aTokenPool,$aTokenPoolIter,$aState) ;
			}
			
			$aToken->setBelongsNamespace($aState->currentNamespace()) ;
			$aToken->setBelongsClass($aState->currentClass()) ;
			$aToken->setBelongsFunction($aState->currentFunction()) ;
		}
	}
		
	public function analyzeDeprecated(TokenPool $aObjectContainer)
	{
		$this->bIsPHPCode = false ;
		$this->aClass = null ;
		
		$aObjectPoolIter = $aObjectContainer->iterator() ;
		foreach($aObjectPoolIter as $aObject)
		{
			if( $aObject->tokenType()==T_OPEN_TAG )
			{
				$this->bIsPHPCode = true ;
			}

			if( !$this->bIsPHPCode )
			{
				continue ;
			}
		
			// namespace 声明
			if( $aObject->tokenType()==T_NAMESPACE )
			{
				$this->analyzeNamespaceDeclare(clone $aObjectPoolIter,$aObject) ;
			}
			
			// 函数定义
			else if( $aObject->tokenType()==T_FUNCTION )
			{
				$aFunctionToken = $this->analyzeFunctionDefine(clone $aObjectPoolIter,$aObject) ;
				
				$aObjectContainer->addFunction($aFunctionToken) ;
				
				// 指针移动到函数体前
				$aArgLstClose = $aFunctionToken->argListToken()->theOther() ;
				$aObjectPoolIter->seek(
					$aObjectPoolIter->search($aArgLstClose)
				) ;
			}
			
			// 类定义
			else if( $aObject->tokenType()==T_CLASS )
			{
				$aObjectContainer->addClass(
					$this->analyzeClassDefine(clone $aObjectPoolIter,$aObject)
				) ;
			}
		
			// 函数调用
			else if( $aObject->tokenType()==Token::T_BRACE_ROUND_OPEN )
			{
				$this->analyzeFunctionCall(clone $aObjectPoolIter,$aObject) ;
			}
		

			$this->setTokenBelongs($aObject) ;
			
			// class 定义结束
			if( $this->aClass and $this->aClass->bodyToken()->theOther()===$aObject )
			{
				$this->aClass = null ;
			}
			// function 定义结束
			if( $this->aFunction and $aFuncBody=$this->aFunction->bodyToken() and $aFuncBody->theOther()===$aObject )
			{
				$this->aFunction = null ;
			}
			
			// php 结束标签
			if( $aObject->tokenType()==T_CLOSE_TAG )
			{
				$this->bIsPHPCode = false ;
			}
			
		}
		
		
		
		/*foreach($aObjectPoolIter as $aObject)
		{
			echo $aObject->belongsSignature(), $aObject->tokenTypeName(), ":“", $aObject->sourceCode(), "”<br />\r\n" ;
		}*/
	}
	
	private function analyzeNamespaceDeclare(INonlinearIterator $aObjectPoolIter,Token $aObject)
	{
		$aNewToken = new NamespaceDeclare($aObject) ;
		$this->aNamespace = $aNewToken ;

		foreach( $this->findTokens($aObjectPoolIter,array(Token::T_SEMICOLON,T_WHITESPACE,T_NS_SEPARATOR,T_STRING)) as $aToken )
		{
			if( $aToken->tokenType()==T_STRING )
			{
				$aNewToken->addNameToken($aToken) ;
			}
		}
		
		return $aNewToken ;
	}
	
	private function analyzeFunctionDefine(INonlinearIterator $aObjectPoolIter,Token $aObject)
	{
		$aNewToken = new FunctionDefine( $aObject ) ;
		$this->aFunction = $aNewToken ;
		
		// 函数修饰符
		$arrModifies = $this->findTokens(clone $aObjectPoolIter,array(
				T_PUBLIC ,
				T_PROTECTED ,
				T_PRIVATE ,
				T_STATIC ,
				T_ABSTRACT ,
				T_DOC_COMMENT ,
				T_WHITESPACE ,
		),true) ;
		foreach($arrModifies as $aToken)
		{
			switch ($aToken->tokenType())
			{
				case T_PUBLIC :
					$aNewToken->setAccessToken($aToken) ;
					break ;
				case T_PROTECTED :
					$aNewToken->setAccessToken($aToken) ;
					break ;
				case T_PRIVATE :
					$aNewToken->setAccessToken($aToken) ;
					break ;
				case T_STATIC :
					$aNewToken->setStaticToken($aToken) ;
					break ;
				case T_ABSTRACT :
					$aNewToken->setAbstractToken($aToken) ;
					break ;
				case T_DOC_COMMENT :
					$aDocToken = new DocCommentDeclare($aToken) ;
					$aNewToken->setDocToken($aDocToken) ;
					$aTokenPool->replace($aToken,$aDocToken) ;
					break ;
			}
		}
		
		// 参数列表
		$aNewToken->setNameToken($this->findFirstToken($aObjectPoolIter,T_STRING)) ;
		$aNewToken->setArgListToken($this->findFirstToken($aObjectPoolIter,Token::T_BRACE_ROUND_OPEN)) ;
		
		// 函数体
		$aTokenBody = $this->findFirstToken($aObjectPoolIter,array(Token::T_BRACE_OPEN,Token::T_SEMICOLON)) ;
		if( $aTokenBody and $aTokenBody->tokenType()==Token::T_BRACE_OPEN )
		{
			$aNewToken->setBodyToken($aTokenBody) ;
		}
		
		$aObject->objectPool()->replace($aObject,$aNewToken) ;
		
		return $aNewToken ;
	}
	
	private function analyzeClassDefine(INonlinearIterator $aObjectPoolIter,Token $aObject)
	{
		$aNewToken = new ClassDefine( $aObject ) ;
		$this->aClass = $aNewToken ;
		
		$aNewToken->setNameToken($this->findFirstToken($aObjectPoolIter,T_STRING)) ;
		$aNewToken->setBodyToken($this->findFirstToken($aObjectPoolIter,Token::T_BRACE_OPEN)) ;
		
		$aObject->objectPool()->replace($aObject,$aNewToken) ;
		
		return $aNewToken ;
	}
	
	private function analyzeFunctionCall(INonlinearIterator $aObjectPoolIter,Token $aObject)
	{
		$aObjectPoolIter = clone $aObjectPoolIter ;
		
		// $this->findToken($aObjectPoolIter, array(), true ) ;
	}
	
	
	private function findFirstToken(INonlinearIterator $aObjectPoolIter,$types,$bReverse=false)
	{		
		Type::toArray($types,Type::toArray_ignoreNull) ;
		
		do{
			
			$bReverse?
				$aObjectPoolIter->prev() :
				$aObjectPoolIter->next() ;
			
			if( !$aToken=$aObjectPoolIter->current() )
			{
				return ;
			}
			
			if( in_array($aToken->tokenType(),$types) )
			{
				return $aToken ;
			}
		
		} while(1) ;
	}

	private function findTokens(INonlinearIterator $aObjectPoolIter,$types,$bReverse=false)
	{
		$arrTokens = array() ;
		
		Type::toArray($types,Type::toArray_ignoreNull) ;
		
		do{
			
			$bReverse?
				$aObjectPoolIter->prev() :
				$aObjectPoolIter->next() ;
			
			if( !$aToken=$aObjectPoolIter->current() )
			{
				break ;
			}
		
			if( !in_array($aToken->tokenType(),$types) )
			{
				break ;
			}		
			
			$arrTokens[] = $aToken ;

		} while(1) ;
		
		return $arrTokens ;
	}
	
	private function setTokenBelongs(Token $aToken)
	{
		if($this->aFunction)
		{
			$aToken->setBelongsFunction($this->aFunction) ;
		}
		if($this->aClass)
		{
			$aToken->setBelongsClass($this->aClass) ;
		}
		if($this->aNamespace)
		{
			$aToken->setBelongsNamespace($this->aNamespace) ;
		}
	}
	
	private $bIsPHPCode = false ;
	private $aNamespace ;
	private $aClass ;
	private $aFunction ;
	
	
	private $arrParsers ;
}

?>
