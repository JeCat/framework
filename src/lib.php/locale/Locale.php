<?php

namespace jc\locale ;

use jc\lang\SentencePackage;

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
		$arrArgvs = $argvs===null? array(): (array)$argvs ;
		
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
	 * @see jc\locale.ILocale::number()
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

?>