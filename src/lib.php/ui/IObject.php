<?php

namespace jc\ui ;

use jc\io\IOutputStream;

interface IObject
{
	/**
	 * @return IObject
	 */
	public function parent() ;
	
	public function setParent() ;
	
	public function depth() ;
	
	public function compile(IOutputStream $aDev) ;
}

?>