<?php

namespace jc\ui ;

use jc\pattern\composite\IContainer;
use jc\io\IOutputStream;

interface IObject extends IContainer
{	
	public function depth() ;
	
	public function compile(IOutputStream $aDev) ;
}

?>