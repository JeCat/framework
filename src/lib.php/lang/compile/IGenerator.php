<?php

namespace jc\lang\compile ;

use jc\lang\compile\object\Token;
use jc\lang\compile\object\TokenPool;

interface IGenerator
{
	public function generateTargetCode(TokenPool $aTokenPool, Token $aObject) ;
}

?>