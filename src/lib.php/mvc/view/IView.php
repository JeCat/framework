<?php
namespace jc\mvc\view ;

use jc\mvc\view\widget\IViewWidget;
use jc\pattern\composite\IContainer;
use jc\pattern\composite\Container;

interface IView extends IContainer
{
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
		
	public function addWidget(IViewWidget $aWidget) ;
	
	public function removeWidget(IViewWidget $aWidget) ;
	
	public function clearWidgets() ;
	
	/**
	 * @return \Iterator
	 */
	public function widgitIterator() ;
	
}

?>