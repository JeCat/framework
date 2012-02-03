<?php
namespace org\jecat\framework\message ;

use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\ui\UI;
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
	
	public function display(UI $aUI=null,IOutputStream $aDevice=null,$sTemplateFilename=null) ;
	
	// 组合模式
	public function addChild(IMessageQueue $aMessageQueue) ;
	
	public function removeChild(IMessageQueue $aMessageQueue) ;
	
	public function addChildHolder(IMessageQueueHolder $aMessageQueue) ;
	
	public function removeChildHolder(IMessageQueueHolder $aMessageQueue) ;
}

?>