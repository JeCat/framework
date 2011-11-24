<?php

namespace org\jecat\framework\ui ;

use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\pattern\composite\IContainer;
use org\jecat\framework\pattern\composite\IContainedable;

interface IObject extends IContainer, IContainedable
{
	public function summary() ;
	
	public function printStruct(IOutputStream $aDevice=null,$nDepth=0) ;
}

?>