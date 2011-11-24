<?php

namespace org\jecat\framework\lang\compile ;

use org\jecat\framework\lang\compile\object\Token;
use org\jecat\framework\lang\compile\object\TokenPool;

interface IGenerator
{
	public function generateTargetCode(TokenPool $aTokenPool, Token $aObject) ;
}

?>