<?php

namespace jc\compile ;

use jc\pattern\composite\IContainer;

interface IInterpreter
{
	public function generateTargetCode(IContainer $aObjectContainer) ;
}

?>