<?php

namespace jc\compile\interpreters ;

use jc\iterate\INonlinearIterator;
use jc\compile\object\NamespaceDeclare;
use jc\compile\object\ClassDefine;
use jc\compile\object\Token;
use jc\lang\Exception;
use jc\compile\object\FunctionDefine;
use jc\pattern\composite\IContainer;
use jc\compile\IInterpreter;
use jc\lang\Object;

class SyntaxParser extends Object implements IInterpreter
{
	public function analyze(IContainer $aObjectContainer)
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

			$this->setTokenBelongs($aObject) ;
		
			// namespace 声明
			if( $aObject->tokenType()==T_NAMESPACE )
			{
				$this->analyzeNamespaceDeclare($aObjectPoolIter,$aObject) ;
			}
			
			// 函数定义
			else if( $aObject->tokenType()==T_FUNCTION )
			{
				$this->analyzeFunctionDefine($aObjectPoolIter,$aObject) ;
			}
			
			// 类定义
			else if( $aObject->tokenType()==T_CLASS )
			{
				$this->analyzeClassDefine($aObjectPoolIter,$aObject) ;
			}
			
			
			// class 定义结束
			if( $this->aClass and $this->aClass->bodyToken()->theOther()===$aObject )
			{
				$this->aClass = null ;
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
	
		foreach($this->findToken($aObjectPoolIter, Token::T_SEMICOLON, true) as $aToken)
		{
			if( $aToken->tokenType()==T_STRING )
			{
				$aNewToken->addNameToken($aToken) ;
			}
		}
		
	}
	
	private function analyzeFunctionDefine(INonlinearIterator $aObjectPoolIter,Token $aObject)
	{
		$aNewToken = new FunctionDefine( $aObject ) ;
		$this->aFunction = $aNewToken ;
		
		$aNewToken->setNameToken($this->findToken($aObjectPoolIter,T_STRING)) ;
		$aNewToken->setArgListToken($this->findToken($aObjectPoolIter,Token::T_BRACE_ROUND_OPEN)) ;
		
		$aTokenBody = $this->findToken($aObjectPoolIter,array(Token::T_BRACE_OPEN,Token::T_SEMICOLON)) ;
		if( $aTokenBody->tokenType()==Token::T_BRACE_OPEN )
		{
			$aNewToken->setBodyToken($aTokenBody) ;
		}
		
		$aObject->objectPool()->replace($aObject,$aNewToken) ;
	}
	
	private function analyzeClassDefine(INonlinearIterator $aObjectPoolIter,Token $aObject)
	{
		$aNewToken = new ClassDefine( $aObject ) ;
		$this->aClass = $aNewToken ;
		
		$aNewToken->setNameToken($this->findToken($aObjectPoolIter,T_STRING)) ;
		$aNewToken->setBodyToken($this->findToken($aObjectPoolIter,Token::T_BRACE_OPEN)) ;
		
		$aObject->objectPool()->replace($aObject,$aNewToken) ;
	}
	
	
	private function findToken(INonlinearIterator $aObjectPoolIter,$types,$bReturnAllTokens=false)
	{
		if($bReturnAllTokens)
		{
			$arrTokens = array() ;
		}
		
		$types = (array)$types ;
		
		do{
			
			$aObjectPoolIter->next() ;
			
			if( !$aToken=$aObjectPoolIter->current() )
			{
				foreach($types as &$t)
				{
					if(is_numeric($t))
					{
						$t = token_name($t) ;
					}
				}
				throw new Exception("无法找到指定的 token ; type:%s",implode(",",$types)) ;
			}
						
			$this->setTokenBelongs($aToken) ;
			
			if($bReturnAllTokens)
			{
				$arrTokens[] = $aToken ;
			}
			
			if( in_array($aToken->tokenType(),$types) )
			{
				if($bReturnAllTokens)
				{
					return $arrTokens ;
				}
				else 
				{
					return $aToken ;
				}
			}
		
		} while(1) ;
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
}

?>