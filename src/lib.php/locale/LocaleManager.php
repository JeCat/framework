<?php

namespace jc\lang ;

use jc\fs\FSO;
use jc\fs\FSOIterator;

class LocaleManager
{
	public function __construct($sDefaultLocaleName)
	{
		$this->setDefaultLocaleName($sDefaultLocaleName) ;
	}

	public function defaultLocaleName()
	{
		return $this->sDefaultLocaleName ;
	}
	public function setDefaultLocaleName($sDefaultLocaleName)
	{
		$this->sDefaultLocaleName = $sDefaultLocaleName ;
		$this->addLanguage($sDefaultLocaleName) ;
	}

	public function createSentenceTar($sLocaleName)
	{
		return $this->arrLocales[$sLocaleName] = new SentenceTar($sLocaleName) ;
	} 
	public function addLanguage($sLocaleName)
	{
		if( !$this->existsLocale($sLocaleName) )
		{
			$this->createSentenceTar($sLocaleName) ;
		}
		
		return $this->localeTar($sLocaleName) ;
	}
	public function existsLocale($sLocaleName)
	{
		return isset($this->arrLocales[$sLocaleName]) ;
	}
	public function localeTar($sLocaleName)
	{
		return $this->arrLocales[$sLocaleName]?:null ;
	}
	
	
	public function loadSentenceFolder($sFolderPath)
	{
		$aIter = FSOIterator::createFileIterator($sFolderPath) ;
		for($aIter->rewind;$aIter->valid();$aIter->next())
		{
			$sFilename = $aIter->filename() ;
			$arr=explode(".", $sFilename) ;
			if(array_pop($arr)!='spkg')
			{
				continue ;
			}
			
			$sLocaleName = strtolower(array_pop($arr)) ;
			$sPackageName = implode(".", $arr) ;
			
			if( empty($sLocaleName) or empty($sPackageName) )
			{
				continue ;
			}
			
			$this->loadSentencePackage($aIter->current(),$sPackageName,$sLocaleName) ;
		}
	}
	
	public function loadSentencePackage($sPath,$sPackageName,$sLocaleName=null)
	{
		$aPackageTar = $this->addLanguage($sLocaleName?:$this->sDefaultLocaleName) ;
		$aPackageTar->loadPackage($sPath,$sPackageName) ;
	}
	
	
	public function sentence($sSentence,array $arrArgvs=array()) ;
	
	
	private $sDefaultLocaleName ;
	
	private $arrLocales = array() ;
}

?>