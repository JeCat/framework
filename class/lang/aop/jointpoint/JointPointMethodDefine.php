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
namespace org\jecat\framework\lang\aop\jointpoint ;

use org\jecat\framework\lang\compile\object\ClosureToken;
use org\jecat\framework\lang\compile\object\FunctionDefine;
use org\jecat\framework\lang\compile\object\Token;

class JointPointMethodDefine extends JointPoint
{
	public function __construct($sClassName=null,$sMethodNamePattern='*',$bMatchDerivedClass=false)
	{
		parent::__construct($sClassName,$sMethodNamePattern,$bMatchDerivedClass) ;
	}
	
	static public function createFromDeclare($sDeclare)
	{
		if( !preg_match('/^([\\w\\\\_]+)::([\\w_]+)\\s*\\(\\s*\\)\\s*(\[derived\])?$/i',$sDeclare,$arrRes) )
		{
			return null ;
		}
		return new self( $arrRes[1], $arrRes[2], @$arrRes[3]?true:false ) ;
	}
	
	public function exportDeclare($bWithClass=true)
	{
		return ($bWithClass?$this->weaveClass():'')."::".$this->weaveMethod().'()' ;
	}
	
	public function matchExecutionPoint(Token $aToken)
	{		
		$bIsPattern = $this->weaveMethodIsPattern() ;
		
		// 模糊匹配每个方法
		if( $bIsPattern and ($aToken instanceof FunctionDefine) )
		{
			// 必须是一个类方法
			if( !$aClass=$aToken->belongsClass() )
			{
				return false ;
			}
			
			if( !$this->matchClass($aClass->fullName()) )
			{
				return false ;
			}
			
			return preg_match( $this->weaveMethodNameRegexp(),$aToken->name() )? true: false ;
		}
		
		// 精确匹配 class token
		else if( !$bIsPattern and ($aToken instanceof ClosureToken) )
		{
			// 必须是一个 "}" 
			if( $aToken->tokenType()!=Token::T_BRACE_CLOSE)
			{
				return false ;
			}
			
			// 必须成对
			if( !$aToken->theOther() ){
				return false;
			}
			
			$aClass=$aToken->theOther()->belongsClass();
			if( null === $aClass ){
				return false;
			}
			
			// 必须做为 class 的结束边界
			if($aToken->theOther()!==$aClass->bodyToken() )
			{
				return false ;
			}
			
			if( !$this->matchClass($aClass->fullName()) )
			{
				return false ;
			}
			
			return true ;
		}
	}
	
}

