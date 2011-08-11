<?php

namespace jc\ui ;

use jc\io\IOutputStream;
use jc\pattern\composite\IContainer;
use jc\pattern\composite\IContainedable;

interface IObject extends IContainer, IContainedable
{
	public function summary() ;
	
	public function printStruct(IOutputStream $aDevice=null,$nDepth=0) ;
}

?>