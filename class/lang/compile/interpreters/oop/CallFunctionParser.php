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

use org\jecat\framework\lang\compile\object\CallFunction;
use org\jecat\framework\pattern\iterate\CallbackFilterIterator;
use org\jecat\framework\pattern\iterate\ReverseIterator;
use org\jecat\framework\pattern\iterate\IReversableIterator;
use org\jecat\framework\pattern\iterate\INonlinearIterator;
use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\lang\compile\object\Token;

class CallFunctionParser implements ISyntaxParser
{
	public function parse(TokenPool $aTokenPool,INonlinearIterator $aTokenPoolIter,State $aState)
	{
		$aTokenPoolIter = clone $aTokenPoolIter ;
		
		$aToken = $aTokenPoolIter->current() ;
		if( $aToken->tokenType()!==Token::T_BRACE_ROUND_OPEN )
		{
			return ;
		}
		
		// 找参数括号前面的函数名
		// ---------------------------------------------
		$aFinderIter = new CallbackFilterIterator(
			new ReverseIterator($aTokenPoolIter)
			, function (IReversableIterator $aTokenPoolIter){
				switch ( $aTokenPoolIter->current()->tokenType() )
				{
					case T_WHITESPACE :		// 过滤空白token
						return false ;
					case T_STRING :			// 预期的token ,函数名
						return true ;
					default:				// 遇到意外的token
						$aTokenPoolIter->last() ;
						return false ;
				}
			}
		) ;
		
		$aFinderIter->next();
		if( !$aCallFunctionName = $aFinderIter->current() )
		{
			return ;
		}
		
		// 检查函数名前面的 function 声明
		// ---------------------------------------------
		$aFinderIter = new CallbackFilterIterator(
			new ReverseIterator(clone $aTokenPoolIter)
			, function (IReversableIterator $aTokenPoolIter){
				switch ( $aTokenPoolIter->current()->tokenType() )
				{
					case T_WHITESPACE :		// 过滤空白token
						return false ;
					case T_FUNCTION :		// 预期的token
						return true ;
					default:				// 遇到意外的token
						$aTokenPoolIter->last() ;
						return false ;
				}				
			}
		) ;
		
		// 找到 function 申明，这是函数定义 而不是函数调用
		$aFinderIter->next();
		if( $aFinderIter->current() )
		{
			return ;
		}
		
		$aCallFunction = new CallFunction($aCallFunctionName,$aToken) ;
		
		// 置换
		$aTokenPool->replace($aCallFunctionName,$aCallFunction) ;
		
		
		// 查找class成员访问符：-> 或 ::
		// ---------------------------------------------
		$aFinderIter = new CallbackFilterIterator(
			new ReverseIterator($aTokenPoolIter)
			, function (IReversableIterator $aTokenPoolIter){
				switch ( $aTokenPoolIter->current()->tokenType() )
				{
					case T_WHITESPACE :				// 过滤空白token
						return false ;
					case T_OBJECT_OPERATOR :		// 预期的token: "->"
						return true ;
					case T_PAAMAYIM_NEKUDOTAYIM :	// 预期的token: "::"
					case T_DOUBLE_COLON:
						return true ;
					default:						// 无效的token
						$aTokenPoolIter->last() ;
						return false ;
				}
			}
		) ;
		
		// 方法
		$aFinderIter->next();
		if( $aAccessSymbol = $aFinderIter->current() )
		{
			$aFinderIter = new CallbackFilterIterator(
				new ReverseIterator($aTokenPoolIter)
				, function (IReversableIterator $aTokenPoolIter){
					// 过滤空白token
					return $aTokenPoolIter->current()->tokenType()!==T_WHITESPACE ;
				}
			) ;
			
			$aFinderIter->next() ;
			$aClass = $aFinderIter->current() ;
			
			$aCallFunction->setClassToken($aClass) ;
			$aCallFunction->setAccessToken($aAccessSymbol) ;
		}
	}	
}

