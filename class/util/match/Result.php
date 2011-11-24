<?php
namespace org\jecat\framework\util\match ;

use org\jecat\framework\lang\Object;

class Result extends Object
{
	public function __construct(array $arrResult)
	{
		$this->arrResult = $arrResult ;
	}
	
	public function content($nGrp=0) 
	{
		return isset($this->arrResult[$nGrp][0])? $this->arrResult[$nGrp][0]: null ;
	}
	
	public function position($nGrp=0) 
	{
		return isset($this->arrResult[$nGrp][1])? $this->arrResult[$nGrp][1]: null ;
	}
	
	public function length($nGrp=0)
	{
		return strlen($this->result($nGrp)) ;
	}
	
	private $arrResult ;
}

?>