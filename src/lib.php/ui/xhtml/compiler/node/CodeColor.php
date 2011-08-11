<?php
namespace jc\ui\xhtml\compiler\node ;

use jc\io\IOutputStream;

class CodeColor
{
	public function outputFilter($sData)
	{
		$this->sCode.= $sData ;
		
		return array(null) ;
	}
	
	public function output(IOutputStream $aDev)
	{
		$aDev->write( highlight_string($this->sCode,true) ) ;
	}
	
	private $sCode ;
}

?>