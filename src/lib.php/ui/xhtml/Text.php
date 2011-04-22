<?php

namespace jc\ui\xhtml ;


use jc\util\IDataSrc;

use jc\ui\IDisplayDevice;

use jc\ui\Object;

class Text extends Object
{
	static public function type()
	{
		return __CLASS__ ;
	}
	
	public function __construct($sText)
	{
		$this->setText($sText) ;
	}

	public function text()
	{
		return $this->sText ;
	}
	public function setText($sText)
	{
		$this->sText = $sText ;
	}
	
	public function render(IDisplayDevice $aDev,IDataSrc $aVariables) 
	{
		$aDev->write($this->sText) ;
	}
	
	private $sText ;
}

?>