<?php
namespace org\jecat\framework\util ;

use org\jecat\framework\lang\Exception ;

class StopFilterSignal extends Exception
{
	public function __construct($ReturnVariables=null)
	{
		$this->ReturnVariables =& $ReturnVariables ;
	}
	
	public function returnVariables()
	{
		return $this->ReturnVariables ;
	}
	
	static public function stop($return=null)
	{
		throw new StopFilterSignal($return) ;  
	} 
	
	private $ReturnVariables ;
}

?>