<?php

namespace jc\lang ;

class SentencePackage
{
	public function __construct($sLocaleName,$sPackageName,$sPackagePath=null)
	{
		$this->sLocaleName = $sLocaleName ;
		$this->sPackageName = $sPackageName ;
		
		if($sPackagePath)
		{
			$this->loadCompiled($sPath,$bCheckSyntax=true) ;
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
	
	public function loadPackage($sPath,$bCheckSyntax=true)
	{
		if( !is_file($sPath) or !is_readable($sPath) )
		{
			throw new Exception("语言包文件不存在(或无法访问)：%s", $sPath) ;
		}
		if( $bCheckSyntax and !php_check_syntax($sPath) )
		{
			throw new Exception("语言包文件内容无效：%s", $sPath) ;
		}
		
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

?>