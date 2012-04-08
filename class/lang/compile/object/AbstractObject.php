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
namespace org\jecat\framework\lang\compile\object ;

use org\jecat\framework\pattern\composite\Composite;

class AbstractObject extends Composite
{
	public function __construct($sSource,$nPostion,$nLine=0)
	{
		$this->sSource = $this->sTarget = $sSource ;
		$this->nPostion = $nPostion ;
		$this->nLine = $nLine ;
	}
	
	public function __toString()
	{
		return $this->sourceCode() ;
	}

	public function sourceCode()
	{
		return $this->sSource ;
	}
	public function setSourceCode($sCode)
	{
		$this->sSource = $sCode ;
	}

	public function targetCode()
	{
		return $this->sTarget ;
	}
	public function setTargetCode($sCode)
	{
		$this->sTarget = $sCode ;
	}

	public function position()
	{
		return $this->nPostion ;
	}
	public function setPosition($nPostion)
	{
		$this->nPostion = $nPostion ;
	}
	public function length()
	{
		return strlen($this->sSource) ;
	}
	public function endPosition()
	{
		return $this->position() + $this->length() - 1 ;
	}
	public function line()
	{
		return $this->nLine ;
	}
	public function setLine($nLine)
	{
		$this->nLine = $nLine ;
	}
	
	private $nLine ;
	
	private $sSource ;
	
	private $sTarget ;
	
	private $nPostion ;
}

