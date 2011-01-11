<?php
namespace jc\system ;

use jc\io\PrintStream;
use jc\lang\Object ;

class Response extends Object
{	
	public function __construct(PrintStream $aPrinter)
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