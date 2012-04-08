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
use org\jecat\framework\lang\SentencePackage;

class Locale implements ILocale
{
	public function __construct($sLocaleName)
	{
		$this->sLocaleName = $sLocaleName ;
	}
	
	public function localeName()
	{
		return $this->sLocaleName ;
	}
	
	
	// for language
	public function loadSentencePackage($sPath,$sPackageName)
	{
		$this->arrSentencePackages[$sPackageName][] = new SentencePackage($this->localeName(), $sPackageName,$sPath) ;
	}

	public function sentencePackage($sName)
	{
		return isset($this->arrSentencePackages[$sName])?end($this->arrSentencePackages[$sName]):null ;
	}
	
	public function removePackage($sName,$bTotal=true)
	{
		if($bTotal)
		{
			unset($this->arrSentencePackages[$sName]) ;
		}
		else 
		{
			array_pop($this->arrSentencePackages[$sName]) ;
			if( empty($this->arrSentencePackages[$sName]) )
			{
				unset($this->arrSentencePackages[$sName]) ;
			}
		}
	}
	
	public function findSentence($sKey)
	{
		if(!$this->arrSentencePackages)
		{
			return null ;
		}
		
		for(end($this->arrSentencePackages);$arrPackages=current($this->arrSentencePackages);prev($this->arrSentencePackages))
		{
			for(end($arrPackages);$aPackage=current($arrPackages);prev($arrPackages))
			{
				$sSentence = $aPackage->sentence($sKey) ;
				if($sSentence!==null)
				{
					reset($this->arrSentencePackages) ;
					return $sSentence ;
				}
			}
		}
		
		reset($this->arrSentencePackages) ;
		return null ;
	}
	
	public function trans($sOri,$argvs=null,$sSavePackageName=null)
	{
		$arrArgvs = Type::toArray($argvs) ;
		
		$sSentence = $this->findSentence($sOri) ;
		
		if($sSentence===null)
		{
			$sSentence = $sOri ;
			
			$aPackage = $this->sentencePackage($sSavePackageName) ;
			if( $aPackage )
			{
				$aPackage->addSentence($sSentence,$sSentence) ;
			}
		}
		
		$sWord = call_user_func_array('sprintf', array_merge(array($sOri),$arrArgvs)) ;
		return $sWord?: $sOri ;
	}

	/**
	 * @see org\jecat\framework\locale.ILocale::number()
	 */
	public function number($Number)
	{
		return $Number ;
	}
	
	public function telNumber($Number)
	{}
	
	private $sLocaleName ;
	
	private $arrSentencePackages ;
}

