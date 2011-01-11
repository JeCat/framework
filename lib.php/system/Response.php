<?php
namespace jc\system ;

use jc\lang\Object ;
use jc\io\PrintSteam;

class Response extends Object
{	
	public function initialize(PrintSteam $aPrinter)
	{
		$this->aPrinter = $aPrinter ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return jc\io\PrintSteam
	 */
	public function printer()
	{
		return $this->aPrinter ;
	}

	/**
	 * Enter description here ...
	 * 
	 * @return jc\io\PrintSteam
	 */
	public function output($sBytes)
	{
		$this->aPrinter->write($sBytes) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @var jc\io\PrintSteam
	 */
	private $aPrinter ;
}

?>