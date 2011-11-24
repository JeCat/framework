<?php
namespace jc\system ;

use jc\util\IFilterMangeger;
use jc\util\DataSrc ;

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
	 * @var jc\io\PrintSteam
	 */
	private $aPrinter ;
}

?>