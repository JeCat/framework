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
namespace org\jecat\framework\ui ;

use org\jecat\framework\io\IOutputStream;

class VariableDeclares
{
	public function declareVarible($sVarName,$sInitExpression=null)
	{
		$this->arrDeclareVariables[$sVarName] = $sInitExpression ;
	}
	
	public function hasDeclared($sVarName)
	{
		return array_key_exists($sVarName,$this->arrDeclareVariables) ;
	}
	
	public function make(IOutputStream $aDev)
	{
		foreach($this->arrDeclareVariables as $sVarName=>&$sInitExpression)
		{
			if($sInitExpression!==null)
			{
				$aDev->write("\${$sVarName} = {$sInitExpression} ;") ;
			}
			else
			{
				$aDev->write("\${$sVarName} ;") ;
			}
		}
	}
	
	public function variableNameIterator()
	{print_r(array_keys($this->arrDeclareVariables)) ;
		return $this->arrDeclareVariables?
					new \ArrayIterator(array_keys($this->arrDeclareVariables)) :
					new \EmptyIterator() ;
	}
	
	private $arrDeclareVariables = array() ;
}

