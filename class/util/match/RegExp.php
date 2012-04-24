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

namespace org\jecat\framework\util\match ;

use org\jecat\framework\util\String;
use org\jecat\framework\lang\Object;

class RegExp extends Object
{
	public function __construct($sFullRegExp)
	{
		$this->sFullRegExp = $sFullRegExp ;
	}
	
	public function fullRegExp() 
	{
		return $this->sFullRegExp ;
	}
	
	function setFullRegExp($sFullRegExp) 
	{
		$this->sFullRegExp = $sFullRegExp ;
	}
	
	/**
	 * @return ResultSet
	 */
	function match($sSource,$nLimit=-1)
	{
		$arrResult = array() ;
		
		if($nLimit==1)
		{
			if(!preg_match($this->fullRegExp(),$sSource,$arrResult,PREG_OFFSET_CAPTURE))
			{
				return null ;
			}
			$arrResult = array( $arrResult ) ;
		}
		
		else
		{
			if(!preg_match_all($this->fullRegExp(),$sSource,$arrResult,PREG_SET_ORDER|PREG_OFFSET_CAPTURE))
			{
				return null ;
			}
			
			if($nLimit>0)
			{
				$arrResult = array_slice($arrResult,0,$nLimit) ;
			}
		}
		
		$aResSet = new ResultSet() ;
		
		foreach($arrResult as $arrOneResult)
		{
			$aResSet->add( new Result($arrOneResult) ) ;
		}
		
		return $aResSet ;
	}
	
	public function callbackReplace($Source,$callback,$nLimit=-1)
	{
		$sSource = strval($Source) ;
		
		$aResSet = $this->match($sSource,$nLimit) ;
		$aResSet->reverse() ;
		
		foreach($aResSet as $aRes)
		{
			$sTo = call_user_func_array($callback, array($aRes)) ;
			$sSource = substr_replace($sSource,$sTo,$aRes->position(),$aRes->length()) ;
		}
		
		if($Source instanceof String)
		{
			$Source->set($sSource) ;
			return $Source ;
		}
		else 
		{
			return $sSource ;
		}
	}
	public function replace($sSource,$sTo,$nLimit=-1)
	{
		
	}
	
	public function split($sSource,$nLimit=-1)
	{
		
	}
	
	private $sFullRegExp ;
}


