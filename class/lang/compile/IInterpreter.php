<?php

namespace jc\lang\compile ;

use jc\lang\compile\object\TokenPool;

interface IInterpreter
{
	public function analyze(TokenPool $aTokenPool) ;
}

?>