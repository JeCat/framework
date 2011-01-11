<?php
namespace jc\mvc ;

interface IControllerFactory
{
	/**
	 * Enter description here ...
	 * 
	 * @return IController
	 */
	public function createController($sClassName,$sNamespace='\\') ;
	
}
?>