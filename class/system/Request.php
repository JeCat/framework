<?php
namespace org\jecat\framework\system ;

use org\jecat\framework\util\IFilterMangeger;
use org\jecat\framework\util\DataSrc ;

class Request extends DataSrc
{
	public function get($sName,$default=null)
	{
		$value = parent::get($sName);
		
		if( $value===null and $default!==null )
		{
			$value = $default ;
			$this->set($sName,$value) ;
		}
		
		return $value ;
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
}

?>