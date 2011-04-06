<?php

namespace jc\lang ;

class Locale
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
		$this->addLocale($sDefaultLocaleName) ;
	}

	public function createLocale($sLocaleName)
	{
		return $this->arrLocales[$sLocaleName] = new SentenceTar($sLocaleName) ;
	}
	public function addLocale($sLocaleName)
	{
		if( !$this->existsLocale($sLocaleName) )
		{
			$this->createLocale($sLocaleName) ;
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
		if( !is_dir($sFolderPath) )
		{
			
		}
	}
	
	public function loadSentencePackage($sPath,$sPackageName,$sLocaleName=null)
	{
		$aPackageTar = $this->addLocale($sLocaleName?:$this->sDefaultLocaleName) ;
		$aPackageTar->loadPackage($sPath,$sPackageName) ;
	}
	
	private $sDefaultLocaleName ;
	
	private $arrLocales = array() ;
}

?>