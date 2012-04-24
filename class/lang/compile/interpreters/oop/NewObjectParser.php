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

use org\jecat\framework\lang\compile\object\NewObject;
use org\jecat\framework\pattern\iterate\INonlinearIterator;
use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\lang\compile\object\Token;

class NewObjectParser implements ISyntaxParser
{
	public function parse(TokenPool $aTokenPool,INonlinearIterator $aTokenPoolIter,State $aState)
	{
		$aTokenPoolIter = clone $aTokenPoolIter ;
		
		$aToken = $aTokenPoolIter->current() ;
		if( $aToken->tokenType()!==T_NEW)
		{
			return ;
		}
		$aNewToken = $aToken;
		
		// 找到new后面的类名
		do{ $aTokenPoolIter->next() ; }
		while( $aToken=$aTokenPoolIter->current() and !( $aToken->tokenType()==T_VARIABLE or $aToken->tokenType()==T_STRING or $aToken->tokenType()==T_NAMESPACE ) );
		
		if( !$aClassName = $aTokenPoolIter->current() )
		{
			return ;
		}
		
		$aNewObject = new NewObject( $aNewToken , $aClassName ) ;
		
		// 置换
		$aTokenPool->replace( $aNewToken , $aNewObject ) ;
		
		// 把属性列表"告诉"NewObject
		do{ $aTokenPoolIter->next() ; }
		while( $aToken=$aTokenPoolIter->current() and $aToken->tokenType()!=Token::T_BRACE_ROUND_OPEN ) ;
		
		$aNewObject->setArgvToken($aToken) ;
	}
}

