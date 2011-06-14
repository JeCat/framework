<?php
namespace jc\mvc\view ;

use jc\mvc\view\htmlresrc\HtmlResourcePool;

use jc\message\IMessageQueueHolder;
use jc\io\IOutputStream;
use jc\util\IHashTable;
use jc\mvc\model\IModel;
use jc\mvc\view\widget\IViewWidget;
use jc\pattern\composite\IContainer;
use jc\pattern\composite\Container;

interface IView extends IContainer, IMessageQueueHolder
{
	/**
	 * @return IModel
	 */
	public function model() ;
	
	public function setModel(IModel $aModel) ;
	
	/**
	 * @return jc\util\IHashTable
	 */
	public function variables() ;
	
	public function setVariables(IHashTable $aVariables) ;
	
	/**
	 * @return IViewOutputStream
	 */
	public function outputStream() ;
	
	public function setOutputStream(IOutputStream $aDev) ;

	public function render() ;
	
	public function display() ;
	
	public function show() ;
		
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
	
	public function requireResources(HtmlResourcePool $aResourcePool) ;
	
}

?>