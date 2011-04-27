<?php
namespace jc\util ;

use jc\lang\Exception ;

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
	
	private $ReturnVariables ;
}

?>