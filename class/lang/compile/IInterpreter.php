<?php

namespace org\jecat\framework\lang\compile ;

use org\jecat\framework\lang\compile\object\TokenPool;

interface IInterpreter
{
	public function analyze(TokenPool $aTokenPool) ;
}

?>