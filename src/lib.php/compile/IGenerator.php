<?php

namespace jc\compile ;

use jc\compile\object\AbstractObject;

interface IGenerator
{
	public function generateTargetCode(AbstractObject $aObject) ;
}

?>