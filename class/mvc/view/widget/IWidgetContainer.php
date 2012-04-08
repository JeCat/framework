<?php
namespace org\jecat\framework\mvc\view\widget ;

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
	 * @return org\jecat\framework\pattern\iterate\INonlinearIterator
	 */
	public function widgitIterator() ;
	
}

?>