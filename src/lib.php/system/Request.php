<?php
namespace jc\system ;

use jc\util\DataSrc ;

class Request extends DataSrc
{
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
}

?>