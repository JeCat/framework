<?php

namespace jc\locale ;

use jc\lang\SentencePackage;

class SentenceTar
{
	public function __construct($sLocaleName)
	{
		$this->sLocaleName = $sLocaleName ;
	}
	
	public function localeName()
	{
		return $this->sLocaleName ;
	}
	
	public function loadPackage($sPath,$sPackageName)
	{
		$this->arrPackages[] = new SentencePackage(localeName(), $sPackageName,$sPath) ;
	}
	
	public function sentence($sKey)
	{
		for(end($this->arrPackages);$aPackage=current($this->arrPackages);prev($this->arrPackages))
		{
			$sSentence = $aPackage->sentence($sKey) ;
			if($sSentence!==null)
			{
				reset($this->arrPackages) ;
				return $sSentence ;
			}
			
		}
		
		reset($this->arrPackages) ;
		return null ;
	}

	
	private $sLocaleName ;
	
	private $arrPackages ;
}

?>