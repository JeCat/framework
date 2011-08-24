<?php

namespace jc\compile ;

use jc\compile\object\Token;
use jc\compile\object\TokenPool;

interface IGenerator
{
	public function generateTargetCode(TokenPool $aTokenPool, Token $aObject) ;
}

?>