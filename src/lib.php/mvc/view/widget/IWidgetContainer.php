<?php
namespace jc\mvc\view\widget ;

interface IWidgetContainer {
	
	/**
	 * @return IViewWidget
	 */
	public function addWidget(IViewWidget $aWidget) ;
	
	public function removeWidget(IViewWidget $aWidget) ;
	
	public function clearWidgets() ;
	
	public function hasWidget(IViewWidget $aWidget) ;
	
	/**
	 * @return IViewWidget
	 */
	public function widget($sId) ;
	
	/**
	 * @return \Iterator
	 */
	public function widgitIterator() ;
	
}

?>