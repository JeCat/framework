<?php

namespace jc\compile ;

use jc\compile\object\TokenPool;

interface IInterpreter
{
	public function analyze(TokenPool $aObjectContainer) ;
}

?>