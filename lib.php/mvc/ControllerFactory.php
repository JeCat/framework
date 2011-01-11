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
	public function createController($sClassName)
	{
		$aController = $this->create($sClassName) ;
		
		// other init things
		
		return $aController ;
	}
}

?>