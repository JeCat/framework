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
			if( T_INTERFACE !== $aOriToken->tokenType() 
					&& T_CLASS !== $aOriToken->tokenType() )
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
			
			// init extends implements body
			$sState = 'init' ;
			
			// parent/body
			$aClassBodyToken = $aParentNameToken = null ;
			
			for( $aTokenPoolIter->next() ; $aToken = $aTokenPoolIter->current(); $aTokenPoolIter->next() ){
				// 控制触发 addParentClassName 操作
				$sAddParentClassName = false ;
				
				// 分析 tokenType
				switch( $aToken->tokenType() ){
				case Token::T_BRACE_OPEN:
					$aClassBodyToken = $aToken ;
					$sAddParentClassName = $sState ;
					$sState = 'body' ;
					break(2) ;
				case T_EXTENDS:
					$sAddParentClassName = $sState ;
					$sState = 'extends' ;
					break;
				case T_IMPLEMENTS:
					$sAddParentClassName = $sState ;
					$sState = 'implements' ;
					break;
				case T_STRING:
				case T_NS_SEPARATOR:
					switch($sState){
					case 'extends':
					case 'implements':
						if(!$aParentNameToken)
						{
							$aParentNameToken = new NamespaceString(0,'') ;
							$aParentNameToken->setBelongsNamespace($aState->currentNamespace()) ;
						}
						$aParentNameToken->addNameToken($aToken) ;
						break;
					}
					break;
				case Token::T_COLON:
				case T_WHITESPACE:
					$sAddParentClassName = $sState ;
					break;
				}
				
				// addParentClassName
				if( $sAddParentClassName && $aParentNameToken ){
					switch($sAddParentClassName){
					case 'init':
						break;
					case 'extends':
						$aNewToken->addParentClassName( $aParentNameToken->findRealName($aTokenPool) ) ;
						$aParentNameToken = null ;
						break;
					case 'implements':
						$aNewToken->addImplementsInterfaceName( $aParentNameToken->findRealName($aTokenPool) ) ;
						$aParentNameToken = null ;
						break;
					case 'body':
						break;
					}
				}
			}
			
			if(!$aClassBodyToken)
			{
				throw new ClassCompileException(null,$aOriToken,"编译class: %s时遇到了错误，class没有body",$aNewToken->name()) ;
			}
			$aNewToken->setBodyToken($aClassBodyToken) ;
			
			// 完成
			$aTokenPool->replace($aOriToken,$aNewToken) ;
			$aState->setCurrentClass($aNewToken) ;
						
			$aTokenPool->addClass($aNewToken) ;
		}
		
	}
}

