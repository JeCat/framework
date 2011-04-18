<?php

namespace jc\ui ;

use jc\util\IDataSrc;

interface IObject
{
	public function render(IDisplayDevice $aDev,IDataSrc $aVariables) ;
}

?>