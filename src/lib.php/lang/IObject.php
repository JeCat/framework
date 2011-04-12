<?php

namespace jc\lang ;

use jc\system\Application ;

interface IObject
{
	/**
	 * Enter description here ...
	 * 
	 * @return stdClass
	 */
	public function create($sClassName,$sNamespace='\\',array $arrArgvs=array()) ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return jc\system\Application
	 */
	public function application($bDefaultGlobal=true) ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return jc\system\Application
	 */
	public function setApplication(Application $aApp) ;
}

?>