<?php
namespace org\jecat\framework\system ;

use org\jecat\framework\util\IFilterMangeger;
use org\jecat\framework\io\PrintStream;
use org\jecat\framework\lang\Object ;

class Response extends Object
{	
	public function __construct(PrintStream $aPrinter)
	{
		$this->aPrinter = $aPrinter ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return org\jecat\framework\io\PrintSteam
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
		
		$this->aPrinter->println($sBytes) ;
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
	 * @var org\jecat\framework\io\PrintSteam
	 */
	private $aPrinter ;
	
	private $aFilters ;
}

?>