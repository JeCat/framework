<?php
namespace org\jecat\framework\lang\compile\interpreters\oop ;

use org\jecat\framework\lang\compile\object\NamespaceString;
use org\jecat\framework\lang\compile\object\DocCommentDeclare;
use org\jecat\framework\lang\compile\ClassCompileException;
use org\jecat\framework\pattern\iterate\INonlinearIterator;
use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\lang\compile\object\Token;
use org\jecat\framework\lang\compile\object\ClassDefine;

class ClassDefineParser implements ISyntaxParser
{
	public function parse(TokenPool $aTokenPool,INonlinearIterator $aTokenPoolIter,State $aState)
	{
		if( !$aOriToken=$aTokenPoolIter->current() )
		{
			return ;
		}
		
		// 已经处于 class 状态中
		if( $aClass=$aState->currentClass() )
		{
			// 遇到
			if( $aOriToken===$aClass->bodyToken()->theOther() )
			{
				$aState->setCurrentClass(null) ;
			}
			
			return ; 
		}
		
		// 
		else 
		{
			if( $aOriToken->tokenType()!=T_CLASS )
			{
				return ;
			}
				
			$aTokenPoolIter = clone $aTokenPoolIter ;
			$aNewToken = new ClassDefine($aOriToken) ;
			
			// class name
			do{ $aTokenPoolIter->next() ; }
			while( $aToken=$aTokenPoolIter->current() and $aToken->tokenType()!=T_STRING ) ;
			
			$aNewToken->setNameToken($aToken) ;
			$aNewToken->setBelongsNamespace($aState->currentNamespace()) ;
			
			// doc comment and abstract
			for(
				$aTokenPoolIter->prev() ;
				$aToken=$aTokenPoolIter->current() ;
				$aTokenPoolIter->prev()
			)
			{
				switch ($aToken->tokenType())
				{
					case T_ABSTRACT :
						$aNewToken->setAbstract(true);
						break ;
					case T_DOC_COMMENT :
						$aDocToken = new DocCommentDeclare($aToken) ;
						$aNewToken->setDocToken($aDocToken) ;
						$aTokenPool->replace($aToken,$aDocToken) ;
						break ;
					case T_WHITESPACE:
					case T_CLASS:
						break;
					default:
						break(2);
				}
			}
			// parent/body
			$aClassBodyToken = $aParentNameToken = null ;
			$bFoundExtendsKeyword = $bParentClassNameOver = false ;
			for( $aTokenPoolIter->next(); $aToken=$aTokenPoolIter->current(); $aTokenPoolIter->next() )
			{
				if($aToken->tokenType()==Token::T_BRACE_OPEN)
				{
					$aClassBodyToken = $aToken ;
					if($aParentNameToken)
					{
						$bParentClassNameOver = true ;
					}
					break ;
				}
				else if($aToken->tokenType()==T_EXTENDS)
				{
					$bFoundExtendsKeyword = true ;
				}
				else if( $bFoundExtendsKeyword and !$bParentClassNameOver )
				{
					$type = $aToken->tokenType() ; 
					if(!$aParentNameToken)
					{
						$aParentNameToken = new NamespaceString(0,'') ;
						$aParentNameToken->setBelongsNamespace($aState->currentNamespace()) ;
					}
					else if( $type==T_STRING or $type==T_NS_SEPARATOR )
					{
						$aParentNameToken->addNameToken($aToken) ;
					}
					else if( $type==T_WHITESPACE ) // 遇到空白字符结束
					{ }
					else
					{
						$bParentClassNameOver = true ;
					}
				}
			}
			
			if(!$aClassBodyToken)
			{
				throw new ClassCompileException($aOriToken,"编译class: %s时遇到了错误，class没有body",$aNewToken->name()) ;
			}
			$aNewToken->setBodyToken($aClassBodyToken) ;
			
			if($bFoundExtendsKeyword)
			{
				if( !$aParentNameToken or !$bParentClassNameOver )
				{
					throw new ClassCompileException(null,$aToken,"编译class: %s时遇到了错误，extends 关键词后没有找到 parent class name",$aNewToken->name()) ;
				}
				$aNewToken->setParentClassName( $aParentNameToken->findRealName($aTokenPool) ) ;
			}
	
			$aParentNameToken ;
			
			// 完成
			$aTokenPool->replace($aOriToken,$aNewToken) ;
			$aState->setCurrentClass($aNewToken) ;
						
			$aTokenPool->addClass($aNewToken) ;
		}
		
	}
}

?>
