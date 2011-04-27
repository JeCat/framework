<?php
namespace jc\system ;

use jc\util\IFilterMangeger;
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
	
	public function setPrinter(PrintStream $aPrinter)
	{
		$this->aPrinter = $aPrinter ;
	}

	public function output($sBytes)
	{
		if( $aFilters = $this->filters() )
		{
			list($sBytes) = $aFilters->handle($sBytes) ;
		}
		
		$this->aPrinter->write($sBytes) ;
	}
	
	/**
	 * @return IFilterMangeger
	 */
	public function filters()
	{
		return $this->aFilters ;
	}
	
	public function setFilters(IFilterMangeger $aFilters)
	{
		$this->aFilters = $aFilters ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @var jc\io\PrintSteam
	 */
	private $aPrinter ;
	
	private $aFilters ;
}

?>