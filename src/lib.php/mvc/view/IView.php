<?php
namespace jc\mvc\view ;

use jc\resrc\htmlresrc\HtmlResourcePool;
use jc\message\IMessageQueueHolder;
use jc\io\IOutputStream;
use jc\util\IHashTable;
use jc\mvc\model\IModel;
use jc\mvc\view\widget\IViewWidget;
use jc\mvc\view\widget\IWidgetContainer ;
use jc\pattern\composite\IContainer;
use jc\pattern\composite\Container;

interface IView extends IContainer, IMessageQueueHolder, IWidgetContainer
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
		
	public function enable($bEnalbe=true) ;
	
	public function isEnable() ;
	
	public function requireResources(HtmlResourcePool $aResourcePool) ;
	
}

?>