<?php

namespace org\jecat\framework\lang ;

use org\jecat\framework\system\Application ;

interface IObject
{
	/**
	 * Enter description here ...
	 * 
	 * @return org\jecat\framework\system\Application
	 */
	public function application($bDefaultGlobal=true) ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return org\jecat\framework\system\Application
	 */
	public function setApplication(Application $aApp) ;
}

?>