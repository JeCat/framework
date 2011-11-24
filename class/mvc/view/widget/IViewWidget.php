<?php

namespace jc\mvc\view\widget ;

use jc\resrc\HtmlResourcePool;
use jc\message\IMessageQueueHolder;
use jc\io\IOutputStream;
use jc\util\IHashTable;
use jc\mvc\view\IView;
use jc\ui\UI;

interface IViewWidget extends IMessageQueueHolder
{
	public function title() ;

	public function setTitle($sTitle) ;

	/**
	 * @return IView
	 */
	public function view() ;

	public function setView(IView $aView) ;

	public function id() ;

	public function setId($sId) ;

	public function display(UI $aUI,IHashTable $aVariables=null,IOutputStream $aDevice=null) ;

	public function setAttribute($sName,$sValue) ;
	public function attribute($sName,$sValue) ;
	public function attributeNameIterator() ;
	public function removeAttribute($sName) ;
}

?>