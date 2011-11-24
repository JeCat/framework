<?php
namespace org\jecat\framework\message ;

use org\jecat\framework\util\IFilterMangeger;

interface IMessageQueue
{
	public function add(Message $aMsg) ;
	
	public function iterator() ;
	
	public function count() ;
	
	/**
	 * @return org\jecat\framework\util\IFilterMangeger
	 */
	public function filters() ;
	
	public function setFilters(IFilterMangeger $aFilterManager) ;
}

?>