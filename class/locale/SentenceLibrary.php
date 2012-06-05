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

namespace org\jecat\framework\locale ;

use org\jecat\framework\lang\Type;

class SentenceLibrary
{
	public function __construct($sLanguage,$sCountry,$sLibName,$sSavePackage=null)
	{
		$this->sLanguage = $sLanguage ;
		$this->sCountry = $sCountry ;
				
		$this->sLibName = $sLibName ;
		$this->sSavePackage = $sSavePackage ;
	}
	
	public function localeName()
	{
		return $this->sLocaleName ;
	}
	public function setLocaleName($sLocaleName)
	{
		$this->sLocaleName = $sLocaleName ;
	}

	public function name()
	{
		return $this->sLibName ;
	}
	public function setName($sLibName)
	{
		$this->sLibName = $sLibName ;
	}
	
	public function packageFilename()
	{
		return "{$this->sLanguage}_{$this->sCountry}.{$this->sLibName}.spkg" ;
	}
	
	public function sentenceKeys()
	{
		return array_keys($this->arrSentences) ;
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

	public function loadLibrary(LanguagePackageFolders $aFolders=null)
	{
		if(!$aFolders)
		{
			$aFolders = LanguagePackageFolders::singleton() ;
		}
	
		foreach( $aFolders->packageIterator($this->sLanguage,$this->sCountry,$this->sLibName) as $sPackagePath )
		{
			$this->loadPackage($sPackagePath) ;
		}
	}
	
	public function loadPackage($sPath/*,$bCheckSyntax=true*/)
	{
		if( !is_file($sPath) or !is_readable($sPath) )
		{
			throw new \Exception("language package invalid：{$sPath}") ;
		}
		/*if( $bCheckSyntax and !php_check_syntax($sPath) )
		{
			throw new Exception("语言包文件内容无效：%s", $sPath) ;
		}*/
		if( $arrSentences = (include $sPath) and is_array($arrSentences) )
		{
			$this->arrSentences = array_merge($this->arrSentences,$arrSentences) ;
		}
	}

	public function trans($sOriWords,$argvs=null)
	{
		$arrArgvs = Type::toArray($argvs) ;
		$sKey = md5($sOriWords) ;

		if(!isset($this->arrSentences[$sKey]))
		{
			$sSentence = $sOriWords ;
			$this->arrNewSentences[$sKey] = $sOriWords ;
		}
		else
		{
			$sSentence = $this->arrSentences[$sKey] ; 
		}
	
		return $arrArgvs? call_user_func_array('sprintf',array_merge(array($sSentence),$arrArgvs)): $sSentence ;
	}
	
	//后添加获得sentence方法
	public function arrSentences()
	{
		return $this->arrSentences;
	}
	
	public function arrNewSentences()
	{
		return $this->arrNewSentences;
	}
	
	//后添加
	public function setSentence($sKey,$sValue)
	{
		$this->arrSentences[$sKey]=$sValue;
	}
	
	public function language()
	{
		return $this->sLanguage;
	}
	
	public function country()
	{
		return $this->sCountry;
	}
	
	/**
	 * 返回未归档的语句
	 * 语言库从语言包中加载语句，当向语言库请求一个语言包中不存在的语句时，语言库将该语句搜集在未归档语句数组中。
	 * 该方法返回当前系统运行过程中，遇到的未归档语句数组。
	 * 可用于保存到一个由系统维护的语言中
	 */
	public function & unarchiveSentences()
	{
		return $this->arrNewSentences ;
	}
	
	private $sLanguage ;
	
	private $sCountry ;
	
	private $sLibName ;
	
	private $arrSentences = array() ;
	
	private $arrNewSentences = array() ;
}


