<?php

namespace jc\locale ;

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
		$this->addLocale($sDefaultLocaleName) ;
	}
	
	public function locale($sLocaleName=null)
	{
		if($sLocaleName===null)
		{
			$sLocaleName = $this->sDefaultLocaleName ;
		}
		return $this->arrLocales[$sLocaleName]?:null ;
	}

	public function createLocale($sLocaleName)
	{
		return $this->arrLocales[$sLocaleName] = new Locale($sLocaleName) ;
	}
	public function addLocale($sLocaleName)
	{
		if( !$this->existsLocale($sLocaleName) )
		{
			$this->createLocale($sLocaleName) ;
		}
		
		return $this->locale($sLocaleName) ;
	}
	public function existsLocale($sLocaleName)
	{
		return isset($this->arrLocales[$sLocaleName]) ;
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
		$aLocale = $this->addLocale($sLocaleName?:$this->sDefaultLocaleName) ;
		$aLocale->loadSentencePackage($sPath,$sPackageName) ;
	}	
	
	private $sDefaultLocaleName ;
	
	private $arrLocales = array() ;
}

?>