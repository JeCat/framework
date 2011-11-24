<?php

namespace org\jecat\framework\mvc\view\widget ;

use org\jecat\framework\resrc\HtmlResourcePool;
use org\jecat\framework\message\IMessageQueueHolder;
use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\util\IHashTable;
use org\jecat\framework\mvc\view\IView;
use org\jecat\framework\ui\UI;

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