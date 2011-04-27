<?php

namespace jc\ui\xhtml ;


use jc\ui\ICompiler;
use jc\util\IHashTable;
use jc\io\IOutputStream;
use jc\ui\IDisplayDevice;
use jc\ui\Object;

class Text extends Object
{
	static public function type()
	{
		return __CLASS__ ;
	}
	
	public function __construct($sText,$bHtml=true)
	{
		$this->sText = $sText ;
		$this->bHtml = $bHtml ;
	}

	public function text()
	{
		return $this->sText ;
	}
	public function setText($sText)
	{
		$this->sText = $sText ;
	}
	
	public function render(IDisplayDevice $aDev,IHashTable $aVariables) 
	{
		$aDev->write($this->sText) ;
	}
	
	public function compile(IOutputStream $aDev,ICompiler $aCompiler)
	{
		if( $this->bHtml )
		{
			$sText = preg_replace("/^\\s+/s", " ", $this->sText) ;
			$sText = preg_replace("/\\s+$/s", " ", $sText) ;
			$sText = htmlspecialchars($sText,ENT_COMPAT,'UTF-8') ;
		}
		else 
		{
			$sText = $this->sText ;
		}
		$aDev->write($sText) ;
	}
	
	private $sText ;
	
	private $bHtml ;
}

?>