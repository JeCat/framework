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

use org\jecat\framework\lang\Object;
use org\jecat\framework\lang\Type;

/**
 * 这是一个还不完整的 Locale 类，目前只实现了语言翻译
 */
class Locale implements ITranslator, \Serializable
{
	/**
	 * @return Locale
	 */
	static public function singleton($bCreateNew=true,$createArgvs=null,$sClass=null)
	{
		if(!self::$aSingleton)
		{
			self::$aSingleton = self::createSessionLocale() ;
		}
		return self::$aSingleton ;
	}
	/**
	 * @return Locale
	 */
	static public function setSingleton(self $aLocal)
	{
		self::$aSingleton = $aLocal ;
	}
	/**
	 * @return Locale
	 */
	static public function flyweight($sLanguage,$sCountry)
	{
		if(!isset(self::$arrFlyweights[$sLanguage][$sCountry]))
		{
			self::$arrFlyweights[$sLanguage][$sCountry] = new self($sLanguage,$sCountry) ;
		}
		return self::$arrFlyweights[$sLanguage][$sCountry] ;
	}
	/**
	 * @return Locale
	 */
	static public function setFlyweight($sLanguage,$sCountry,$aLocale)
	{
		self::$arrFlyweights[$sLanguage][$sCountry] = $aLocale ;
	}

	/**
	 * 根据当前会话的 locale 设置创建 Locale对像
	 * locale设置 保存在cookie中
	 */
	static public function createSessionLocale($sDefaultLanguage='zh',$sDefaultCountry='CN',$bAsSingleton=true)
	{	
		$sLanguage = empty($_COOKIE['JC_LC_LANG'])? $sDefaultLanguage: $_COOKIE['JC_LC_LANG'] ;
		$sCountry = empty($_COOKIE['JC_LC_COUNTRY'])? $sDefaultCountry: $_COOKIE['JC_LC_COUNTRY'] ;
		
		$aLocale = self::flyweight($sLanguage,$sCountry) ;
		if($bAsSingleton)
		{
			self::setSingleton($aLocale) ;
		}		
		
		return $aLocale ;
	}
	
	/**
	 * 切换当前会话的 locale 
	 * locale设置 保存在cookie中
	 * 返回新的 Locale 对像
	 * @return Locale
	 */
	static public function switchSessionLocale($sLanguage,$sCountry,$bReplaceSingleton=true)
	{
		$nExpire = time() + 60*60*24*365*10 ;
		setcookie('JC_LC_LANG',$sLanguage,$nExpire) ;
		setcookie('JC_LC_COUNTRY',$sCountry,$nExpire) ;
		
		$aLocale = self::flyweight($sLanguage, $sCountry) ;
		
		if($bReplaceSingleton)
		{
			self::setSingleton($aLocale) ;
		}
		return $aLocale ;
	}

	/**
	 * 返回当前会话的 language 设置
	 * 保存在 cookie中
	 */
	static public function sessionLanguage($sDefaultLanguage='zh')
	{
		return empty($_COOKIE['JC_LC_LANG'])? $sDefaultLanguage: $_COOKIE['JC_LC_LANG'] ;
	}
	/**
	 * 返回当前会话的 country 设置
	 * 保存在 cookie中
	 */
	static public function sessionCountry($sDefaultCountry='CN')
	{
		return empty($_COOKIE['JC_LC_COUNTRY'])? $sDefaultCountry: $_COOKIE['JC_LC_COUNTRY'] ;
	}
	 
	
	public function __construct($sLanguage,$sCountry)
	{
		$this->sLanguage = $sLanguage ;
		$this->sCountry = $sCountry ;
		$this->sLocaleName = $sLanguage.'-'.$sCountry ;
	}
	
	public function localeName()
	{
		return $this->sLocaleName ;
	}
	/**
	 * 返回国家/地区
	 */
	public function country()
	{
		return $this->sCountry ;
	}
	/**
	 * 返回语言
	 */
	public function language()
	{
		return $this->sLanguage ;
	}
	
	/**
	 * 返回 Locale对像中的语言库
	 */	
	public function sentenceLibrary($sLibName='base',$bAutoCreate=true)
	{
		if( !isset($this->arrSentenceLibs[$sLibName]) )
		{
			if($bAutoCreate)
			{
				$this->arrSentenceLibs[$sLibName] = new SentenceLibrary($this->language(),$this->country(),$sLibName) ;
				$this->arrSentenceLibs[$sLibName]->loadLibrary() ;
			}
			else
			{
				return null ;
			}
		}
		
		return $this->arrSentenceLibs[$sLibName] ;
	}

	/**
	 * 返回所有已经加载过的语言库名称
	 */
	public function loadedSentenceLibraryNames()
	{
		return array_keys($this->arrSentenceLibs) ;
	}
	/**
	 * 返回所有已经加载过的语言库
	 */
	public function loadedSentenceLibraries()
	{
		return $this->arrSentenceLibs ;
	}
	
	public function trans($sOriWords,$argvs=null,$sLibName='base')
	{
		$arrArgvs = Type::toArray($argvs) ;
		return $this->sentenceLibrary($sLibName)->trans($sOriWords,$argvs) ;
	}

	/**
	 * @see org\jecat\framework\locale.Locale::number()
	 */
	public function number($Number)
	{
		return $Number ;
	}
	
	public function telNumber($Number)
	{}


	public function serialize ()
	{
		$arrData = array(
				'sLanguage' => $this->sLanguage ,
				'sCountry' => $this->sCountry ,
				'sLocaleName' => $this->sLocaleName ,
		) ;
		
		// 只序列化 base 语言库
		if( isset($this->arrSentenceLibs['base']) )
		{
			$arrData['arrSentenceLibs']['base'] = $this->arrSentenceLibs['base'] ;
		}
		else
		{
			$arrData['arrSentenceLibs'] = array() ;
		}
		
		return serialize($arrData) ;
	}
	
	/**
	 * @param serialized
	 */
	public function unserialize ($serialized)
	{
		$arrData = unserialize($serialized) ;
		
		$this->sLanguage = $arrData['sLanguage'] ;
		$this->sCountry = $arrData['sCountry'] ;
		$this->sLocaleName = $arrData['sLocaleName'] ;
		$this->arrSentenceLibs = $arrData['arrSentenceLibs'] ;
	}
	
	private $sLanguage ;
	private $sCountry ;
	private $sLocaleName ;
	private $arrSentenceLibs = array() ;
	
	static private $arrFlyweights = array() ;
	static private $aSingleton ;
}


