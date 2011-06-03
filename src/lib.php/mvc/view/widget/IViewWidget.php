<?php

namespace jc\mvc\view\widget ;

use jc\io\IOutputStream;
use jc\util\IHashTable;
use jc\mvc\view\IView;
use jc\ui\UI;

interface IViewWidget
{
	/**
	 * @return IView
	 */
	public function view() ;

	public function setView(IView $aView) ;

	public function id() ;

	public function setId($sId) ;

	public function display(UI $aUI,IHashTable $aVariables=null,IOutputStream $aDevice=null) ;

}

?>