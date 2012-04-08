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

namespace org\jecat\framework\lang ;

class SentencePackage
{
	public function __construct($sLocaleName,$sPackageName,$sPackagePath=null)
	{
		$this->sLocaleName = $sLocaleName ;
		$this->sPackageName = $sPackageName ;
		
		if($sPackagePath)
		{
			$this->loadCompiled($sPackagePath,$bCheckSyntax=true) ;
		}
	}
	public function __destruct()
	{
		//$this->compile() ;
	}

	public function localeName()
	{
		return $this->sLocaleName ;
	}
	public function setLocaleName($sLocaleName)
	{
		$this->sLocaleName = $sLocaleName ;
	}

	public function packageName()
	{
		return $this->sPackageName ;
	}
	public function setPackageName($sPackageName)
	{
		$this->sPackageName = $sPackageName ;
	}

	public function packagePath()
	{
		return $this->sPackagePath ;
	}
	
	public function sentence($sKey) 
	{
		return $this->arrSentences[$sKey]?:'' ;
	}
	
	public function hasSentence($sKey) 
	{
		return isset($this->arrSentences[$sKey]) ;
	}
	
	public function addSentence($sKey,$sSentence) 
	{
		$this->arrSentences[$sKey] = $sSentence ;
	}
	
	public function clearSentence() 
	{
		$this->arrSentences = array() ;
	}
	
	public function loadPackage($sPath/*,$bCheckSyntax=true*/)
	{
		if( !is_file($sPath) or !is_readable($sPath) )
		{
			throw new Exception("语言包文件不存在(或无法访问)：%s", $sPath) ;
		}
		/*if( $bCheckSyntax and !php_check_syntax($sPath) )
		{
			throw new Exception("语言包文件内容无效：%s", $sPath) ;
		}*/
		
		$this->clearSentence() ;
		
		$arrSentences = @include $sPath ;
		foreach ($arrSentences as $sKey=>$sSentence)
		{
			$this->addSentence($sKey,$sSentence) ;
		}
		
		$this->sPackagePath = $sPath ;
	}
	
	public function compile($sSavePath=null)
	{
		
	}
	
	private $sPackagePath ;
	
	private $sLocaleName ;
	
	private $sPackageName ;
	
	private $arrSentences = array() ;
}
