<?php
namespace jc\message ;

use jc\util\IFilterMangeger;

interface IMessageQueue
{
	public function add(Message $aMsg) ;
	
	public function iterator() ;
	
	public function count() ;
	
	/**
	 * @return jc\util\IFilterMangeger
	 */
	public function filters() ;
	
	public function setFilters(IFilterMangeger $aFilterManager) ;
}

?>