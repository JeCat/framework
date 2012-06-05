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
//  正在使用的这个版本是：0.8
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

namespace org\jecat\framework\lang ;

class Assert
{
	static public function enable($bEnable=true)
	{
		self::$bEnableAssert = $bEnable? true: false ;
	}
	
	static public function must($expression,$sMessage=null)
	{
		if( !self::$bEnableAssert )
		{
			return ;
		}
		
		if(!$expression)
		{
			if( !$sMessage )
			{
				$sMessage = "程序中的某个位置触发了异常：表达式的值不应该为 false, null, 0 等值" ;
			}
			throw new Exception($sMessage) ;
		}		
	}

	static public function notNull($Types,$sMessage=null)
	{
		if( !self::$bEnableAssert )
		{
			return ;
		}
		
		if($Types===null)
		{
			if( !$sMessage )
			{
				$sMessage = "程序中的某个位置触发了异常：表达式的值不应该为 null " ;
			}
			throw new Exception($sMessage) ;
		}
	}

	static public function isNull($Types,$sMessage=null)
	{
		if( !self::$bEnableAssert )
		{
			return ;
		}
		
		if($Types!==null)
		{
			if( !$sMessage )
			{
				$sMessage = "程序中的某个位置触发了异常：表达式的值不是预期的 null " ;
			}
			throw new Exception($sMessage) ;
		}
	}
	
	static public function wrong($sMessage=null)
	{
		if( !self::$bEnableAssert )
		{
			return ;
		}
		
		if( !$sMessage )
		{
			$sMessage = "程序中的某个位置触发了异常" ;
		}
		throw new Exception($sMessage) ;
	}
	
	static public function type($Types,& $Variable,$sVarName=null)
	{
		if( !self::$bEnableAssert )
		{
			return ;
		}
		
		Type::toArray($Types,Type::toArray_ignoreNull) ;
		if( !Type::check($Types,$Variable) )
		{
			throw new TypeException($Variable,$Types,$sVarName) ;
		}
	}

	static public function isCallback(& $Variable,$bSyntaxOnly=true,$sVarName=null)
	{
		if( !is_callable($Variable,$bSyntaxOnly) )
		{
			throw new TypeException($Variable,$Types,$sVarName) ;
		}
	}
	
	static private $bEnableAssert = true ;
}

