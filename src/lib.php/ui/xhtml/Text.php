<?php

namespace jc\ui\xhtml ;


use jc\ui\ICompiler;
use jc\util\IHashTable;
use jc\io\IOutputStream;
use jc\ui\IDisplayDevice;
use jc\ui\Object;
use jc\ui\xhtml\Compiler ;

class Text extends Object
{
	static public function type()
	{
		return __CLASS__ ;
	}
	
	public function __construct($sText,$nLine,$nPosition)
	{
		$this->sText = $sText ;
		$this->nLine = $nLine ;
		$this->nPosition = $nPosition ;
	}

	public function text()
	{
		return $this->sText ;
	}
	public function setText($sText)
	{
		$this->sText = $sText ;
	}
	
	public function position()
	{
		return $this->nPosition ;
	}
	
	public function render(IDisplayDevice $aDev,IHashTable $aVariables) 
	{
		$aDev->write($this->sText) ;
	}
	
	public function compile(IOutputStream $aDev,ICompiler $aCompiler)
	{
		$sText = $this->sText ;
			
		// 
		if( $aCompiler instanceof Compiler )
		{
			$sText = $aCompiler->expression($sText) ;
		}
		
		/*if( $this->bHtml )
		{
			$sText = preg_replace("/^\\s+/s", " ", $sText) ;
			$sText = preg_replace("/\\s+$/s", " ", $sText) ;
			$sText = htmlspecialchars($sText,ENT_COMPAT,'UTF-8') ;
		}*/
		
		$aDev->write($sText) ;
	}
	
	private $sText ;
	
	private $nLine ;
	
	private $nPosition ;
}

?>