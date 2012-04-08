<?php
namespace org\jecat\framework\ui\xhtml\compiler\node ;

use org\jecat\framework\ui\TargetCodeOutputStream;

class CodeColor
{
	public function outputFilter($sData)
	{
		$this->sCode.= $sData ;
		
		return array(null) ;
	}
	
	public function output(TargetCodeOutputStream $aDev)
	{
		$aDev->output( highlight_string($this->sCode,true) ) ;
	}
	
	private $sCode ;
}

?>