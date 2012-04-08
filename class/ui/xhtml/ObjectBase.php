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
namespace org\jecat\framework\ui\xhtml ;

use org\jecat\framework\util\String;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\Object;

class ObjectBase extends Object implements IObject
{
	const LOCATE_IN = 1 ;
	const LOCATE_OUT = 2 ;
	const LOCATE_FRONT = 3 ;
	const LOCATE_BEHIND = 4 ;

	public function __construct($nPosition,$nEndPosition,$nLine,$sSource)
	{
		$this->setPosition($nPosition) ;
		$this->setEndPosition($nEndPosition) ;
		$this->setLine($nLine) ;
		$this->setSource($sSource) ;
		
		parent::__construct() ;
	}
	
	public function position() 
	{
		return $this->nPosition ;
	}
	public function setPosition($nPosition)
	{
		$this->nPosition = $nPosition ;
	}
	
	public function endPosition()
	{
		return $this->nEndPosition ;
	}
	public function setEndPosition($nEndPosition)
	{
		$this->nEndPosition = $nEndPosition ;
	}
	
	public function line()
	{
		return $this->nLine ;
	}
	public function setLine($nLine) 
	{
		$this->nLine = $nLine ;
	}

	public function source()
	{
		return $this->sSource ;
	}
	public function setSource($sSource)
	{
		$this->sSource = $sSource ;
	}
	

	public function add($aChild,$sName=null,$bTakeover=true)
	{
		Assert::type(__NAMESPACE__.'\\IObject', $aChild) ;		
		parent::add($aChild,$sName,$bTakeover) ;
	}
	
	
	public function summary()
	{
		if( $sSource = $this->source() )
		{
			$sSource = str_replace("\r",'',$sSource) ;
			$sSource = str_replace("\n",'',$sSource) ;
			
			if(strlen($sSource)>60)
			{
				$sSource = substr($sSource,0,30).' ... '.substr($sSource,-30) ;
			}
		}
		else 
		{
			$sSource = '<empty>' ;
		}
		
		return parent::summary()." Line: " . $this->line() . "; Source: \"" . htmlspecialchars($sSource) . "\"" ;
	}
	
	
	
	static public function getLine(String $aSource,$nObjectPos,$nFindStart=0)
	{
		$nFindLen = $nObjectPos-$nFindStart+1 ;

		if( $aSource->length() < $nFindStart+$nFindLen )
		{
			throw new Exception(__METHOD__."() 超过字符范围(源数据长度:%d, 对象位置: %d,find from:%d,find %d)",array(
					$aSource->length()
					, $nObjectPos
					, $nFindStart
					, $nFindLen
			)) ;
		}
		
		return $aSource->substrCount("\n",$nFindStart,$nFindLen) + 1;
	}
	
	private $nPosition = -1 ;
	
	private $nEndPosition = -1 ;
	
	private $nLine ;
	
	private $sSource ;
}

