<?php
namespace org\jecat\framework\util ;

class EventReturnValue
{
	public function __construct($returnValue=null,$bStopEvent=false)
	{
		$this->returnValue = $returnValue ;
		$this->bStopEvent = $bStopEvent ;
	}
	
	static public function returnByRef(& $returnValue=null,$bStopEvent=false)
	{
		$aReturn = new self() ;
		$aReturn->returnValue =& $returnValue ;
		$aReturn->bStopEvent = $bStopEvent ;
		return $aReturn ;
	}

	public function & returnValue()
	{
		return $this->returnValue ;
	}
	public function & stopEvent()
	{
		return $this->bStopEvent ;
	}
	
	private $returnValue ;
	private $bStopEvent = false ;
}

