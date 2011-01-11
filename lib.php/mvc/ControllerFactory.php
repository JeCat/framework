<?php
namespace jc\mvc ;

use jc\lang\Factory ;

class ControllerFactory extends Factory implements IControllerFactory
{
	/**
	 * Enter description here ...
	 * 
	 * @return IController
	 */
	public function createController($sClassName,$sNamespace='\\')
	{
		$aController = $this->create($sClassName,$sNamespace) ;
		
		// other init things
		
		return $aController ;
	}
}

?>