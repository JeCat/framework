<?php
namespace jc\mvc ;

use jc\pattern\composite\IContainer;

interface IView extends IContainer
{
	/**
	 * @return IHashTable
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
	 * @return Container
	 */
	// public function viewContainers() ;
	
}

?>